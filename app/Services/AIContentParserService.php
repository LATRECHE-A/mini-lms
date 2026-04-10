<?php

/**
 * @author abdellah.latreche04@gmail.com | Mini LMS | 2026
 *
 * Service for parsing raw AI JSON output into structured arrays.
 * Handles multi-chapter and legacy flat formats, including mermaid diagrams and AI-suggested sources.
 */

namespace App\Services;

class AIContentParserService
{
    /**
     * Parse raw AI output into a structured array.
     */
    public function parse(string $raw): array
    {
        $json = $this->extractJson($raw);

        if (! $json || (! isset($json['chapter_title']) && ! isset($json['chapters']))) {
            return ['parsed' => false, 'chapter_title' => null, 'chapters' => [], 'subchapters' => [], 'quiz' => null];
        }

        if (isset($json['chapters']) && is_array($json['chapters']) && count($json['chapters']) > 0) {
            return $this->parseMultiChapter($json);
        }

        return $this->parseLegacy($json);
    }

    private function parseMultiChapter(array $json): array
    {
        $chapters = [];
        $flatSubs = [];
        $firstQuiz = null;

        foreach ($json['chapters'] as $chapter) {
            $subchapters = [];
            foreach (($chapter['subchapters'] ?? []) as $sub) {
                $parsed = [
                    'title' => $sub['title'] ?? 'Sans titre',
                    'content' => $sub['content'] ?? '',
                    'image_query' => $sub['image_query'] ?? '',
                    'source_keywords' => $this->normalizeKeywords($sub['source_keywords'] ?? []),
                    'mermaid_diagram' => $this->sanitizeMermaid($sub['mermaid_diagram'] ?? ''),
                    'suggested_sources' => $this->normalizeSources($sub['suggested_sources'] ?? []),
                ];
                $subchapters[] = $parsed;
                $flatSubs[] = $parsed;
            }

            $quiz = null;
            if (isset($chapter['quiz']['questions']) && is_array($chapter['quiz']['questions'])) {
                $quiz = $this->normalizeQuiz($chapter['quiz']);
                if (! $firstQuiz) {
                    $firstQuiz = $quiz;
                }
            }

            $chapters[] = [
                'title' => $chapter['title'] ?? 'Chapitre',
                'subchapters' => $subchapters,
                'quiz' => $quiz,
            ];
        }

        return [
            'parsed' => true,
            'chapter_title' => $json['chapter_title'] ?? ($chapters[0]['title'] ?? 'Formation'),
            'chapters' => $chapters,
            'subchapters' => $flatSubs,
            'quiz' => $firstQuiz,
        ];
    }

    private function parseLegacy(array $json): array
    {
        $subchapters = [];
        foreach (($json['subchapters'] ?? []) as $sub) {
            $subchapters[] = [
                'title' => $sub['title'] ?? 'Sans titre',
                'content' => $sub['content'] ?? '',
                'image_query' => $sub['image_query'] ?? '',
                'source_keywords' => $this->normalizeKeywords($sub['source_keywords'] ?? []),
                'mermaid_diagram' => $this->sanitizeMermaid($sub['mermaid_diagram'] ?? ''),
                'suggested_sources' => $this->normalizeSources($sub['suggested_sources'] ?? []),
            ];
        }

        $quiz = isset($json['quiz']['questions']) ? $this->normalizeQuiz($json['quiz']) : null;

        $chapters = [];
        if (! empty($subchapters)) {
            $chapters[] = [
                'title' => $json['chapter_title'] ?? 'Chapitre 1',
                'subchapters' => $subchapters,
                'quiz' => $quiz,
            ];
        }

        return [
            'parsed' => ! empty($subchapters),
            'chapter_title' => $json['chapter_title'] ?? null,
            'chapters' => $chapters,
            'subchapters' => $subchapters,
            'quiz' => $quiz,
        ];
    }

    private function normalizeQuiz(array $quiz): array
    {
        $questions = [];
        foreach (($quiz['questions'] ?? []) as $q) {
            $options = $q['options'] ?? [];
            $correctIndex = $q['correct_index'] ?? $q['correct_answer'] ?? 0;

            if (is_string($correctIndex)) {
                $idx = array_search($correctIndex, $options);
                $correctIndex = $idx !== false ? $idx : 0;
            }

            $questions[] = [
                'question' => $q['question'] ?? '',
                'options' => array_values($options),
                'correct_index' => max(0, min((int) $correctIndex, count($options) - 1)),
                'explanation' => $q['explanation'] ?? '',
            ];
        }

        return ['title' => $quiz['title'] ?? 'Quiz', 'questions' => $questions];
    }

    private function normalizeKeywords($keywords): array
    {
        if (is_string($keywords)) {
            return [$keywords];
        }
        if (! is_array($keywords)) {
            return [];
        }

        return array_values(array_filter($keywords, 'is_string'));
    }

    /**
     * Clean mermaid code: unescape \\n to real newlines, strip HTML tags, basic validation.
     */
    private function sanitizeMermaid(string $code): string
    {
        $code = trim($code);
        if (empty($code)) {
            return '';
        }

        // Unescape literal \n that AI often returns in JSON strings
        $code = str_replace('\\n', "\n", $code);
        // Strip any HTML tags the AI might have included
        $code = strip_tags($code);
        // Must start with a mermaid keyword
        $validStarts = ['graph ', 'flowchart ', 'sequenceDiagram', 'classDiagram', 'stateDiagram', 'erDiagram', 'gantt', 'pie ', 'mindmap', 'gitgraph'];
        $firstLine = trim(explode("\n", $code)[0]);
        $isValid = false;
        foreach ($validStarts as $start) {
            if (str_starts_with($firstLine, $start)) {
                $isValid = true;
                break;
            }
        }

        return $isValid ? $code : '';
    }

    /**
     * Validate AI-suggested sources structure.
     */
    private function normalizeSources(array $sources): array
    {
        $valid = [];
        foreach ($sources as $src) {
            if (! is_array($src)) {
                continue;
            }
            $url = $src['url'] ?? '';
            $title = $src['title'] ?? '';
            $type = $src['type'] ?? 'article';

            if (empty($url) || empty($title)) {
                continue;
            }
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                continue;
            }
            if (! in_array($type, ['docs', 'article', 'wikipedia', 'other'])) {
                $type = 'article';
            }

            $valid[] = ['title' => mb_substr($title, 0, 200), 'url' => $url, 'type' => $type];
        }

        return array_slice($valid, 0, 4);
    }

    public function toJson(array $structure): string
    {
        return json_encode($structure, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function extractJson(string $content): ?array
    {
        $content = trim($content);
        $stripped = preg_replace('/^```(?:json)?\s*\n?/m', '', $content);
        $stripped = preg_replace('/\n?```\s*$/m', '', trim($stripped));

        $decoded = json_decode($stripped, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{(?:[^{}]|(?:\{(?:[^{}]|(?:\{(?:[^{}]|(?:\{[^{}]*\}))*\}))*\}))*\}/s', $content, $m)) {
            $decoded = json_decode($m[0], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}
