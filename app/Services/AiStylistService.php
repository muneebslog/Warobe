<?php

namespace App\Services;

use App\Models\ClothingItem;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiStylistService
{
    public function __construct(
        private OutfitSuggestionService $outfitService
    ) {}

    /**
     * Suggest outfit, optionally using AI to pick from top 3. Returns same shape as OutfitSuggestionService + optional explanation.
     *
     * @return array{shirt: ClothingItem|null, pant: ClothingItem|null, shalwar_kameez: ClothingItem|null, explanation: string|null}
     */
    public function suggestForUser(User $user, string $eventType): array
    {
        $topCombinations = $this->outfitService->getTopCombinationsForUser($user, $eventType, 3);

        if (empty($topCombinations)) {
            return [
                'shirt' => null,
                'pant' => null,
                'shalwar_kameez' => null,
                'explanation' => null,
            ];
        }

        if (! config('wardrobe.enable_ai', false) || ! config('services.openai.key')) {
            $best = $topCombinations[0];
            return [
                'shirt' => $best['shirt'],
                'pant' => $best['pant'],
                'shalwar_kameez' => $best['shalwar_kameez'],
                'explanation' => null,
            ];
        }

        $chosen = $this->askOpenAi($eventType, $topCombinations);
        if ($chosen !== null) {
            $idx = $chosen['index'];
            $opt = $topCombinations[$idx] ?? $topCombinations[0];
            return [
                'shirt' => $opt['shirt'] ?? null,
                'pant' => $opt['pant'] ?? null,
                'shalwar_kameez' => $opt['shalwar_kameez'] ?? null,
                'explanation' => $chosen['explanation'] ?? null,
            ];
        }

        $best = $topCombinations[0];
        return [
            'shirt' => $best['shirt'],
            'pant' => $best['pant'],
            'shalwar_kameez' => $best['shalwar_kameez'],
            'explanation' => null,
        ];
    }

    /**
     * @param  array<int, array{shirt: ClothingItem|null, pant: ClothingItem|null, shalwar_kameez: ClothingItem|null, score: float}>  $options
     * @return array{index: int, explanation: string}|null
     */
    private function askOpenAi(string $eventType, array $options): ?array
    {
        $lines = [];
        foreach ($options as $i => $opt) {
            $parts = [];
            if ($opt['shalwar_kameez']) {
                $parts[] = $opt['shalwar_kameez']->name . ' (' . $opt['shalwar_kameez']->color . ')';
            } else {
                if ($opt['shirt']) {
                    $parts[] = $opt['shirt']->name . ' (' . $opt['shirt']->color . ')';
                }
                if ($opt['pant']) {
                    $parts[] = $opt['pant']->name . ' (' . $opt['pant']->color . ')';
                }
            }
            $lines[] = 'Option ' . ($i + 1) . ': ' . implode(' + ', $parts);
        }
        $prompt = "User has these outfit options for a " . $eventType . " event:\n" . implode("\n", $lines) . "\n\nWhich option number (1, 2, or 3) is best and why? Reply in exactly two lines: first line the number only, second line a short explanation.";

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(15)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 150,
                ]);

            if (! $response->successful()) {
                Log::warning('OpenAI API error', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $content = $response->json('choices.0.message.content');
            if (! $content) {
                return null;
            }
            $lines = array_map('trim', explode("\n", trim($content)));
            $num = (int) preg_replace('/\D/', '', $lines[0] ?? '1');
            $index = max(0, min($num - 1, count($options) - 1));
            $explanation = $lines[1] ?? null;

            return ['index' => $index, 'explanation' => $explanation];
        } catch (\Throwable $e) {
            Log::warning('AiStylistService OpenAI request failed', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
