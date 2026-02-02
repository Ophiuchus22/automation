<?php

namespace App\Services\Matching;

class Matcher
{
    /**
     * Normalize text for matching
     *
     * @param string $text
     * @param bool $removeBusinessNoise
     * @return string
     */
    public function normalizeText(string $text, bool $removeBusinessNoise = false): string
    {
        // Convert to uppercase
        $text = mb_strtoupper($text, 'UTF-8');
        
        // Trim
        $text = trim($text);
        
        // Replace hyphen variants with space
        $text = preg_replace('/[\-–—]/', ' ', $text);
        
        // Remove punctuation
        $text = preg_replace('/[.,;:\'"()\[\]{}\\/\\\\]/', '', $text);
        
        // Remove role suffixes (as standalone words)
        $roleSuffixes = ['OWNER', 'PROPRIETOR', 'REP', 'REPRESENTATIVE', 'MANAGER', 'PRESIDENT'];
        foreach ($roleSuffixes as $suffix) {
            $text = preg_replace('/\b' . $suffix . '\b/', '', $text);
        }
        
        // Optionally remove business noise words
        if ($removeBusinessNoise) {
            $businessNoise = ['INC', 'CORP', 'CO', 'COMPANY', 'ENTERPRISES', 'TRADING', 'LTD', 'LIMITED', 'CORPORATION'];
            foreach ($businessNoise as $noise) {
                $text = preg_replace('/\b' . $noise . '\b/', '', $text);
            }
        }
        
        // Collapse multiple whitespace to single space
        $text = preg_replace('/\s+/', ' ', $text);
        
        // Final trim
        $text = trim($text);
        
        return $text;
    }

    /**
     * Extract person key from File 1 format (e.g., "EMMANUEL A. MADELO - Owner")
     *
     * @param string $text
     * @return string
     */
    public function extractPersonKeyFromFile1(string $text): string
    {
        // Remove role segments after dash
        if (strpos($text, '-') !== false) {
            $parts = explode('-', $text);
            $text = $parts[0]; // Take only the part before the dash
        }
        
        // Normalize first
        $text = $this->normalizeText($text, false);
        
        // Split into tokens
        $tokens = explode(' ', $text);
        
        // Remove single-letter tokens (middle initials)
        $tokens = array_filter($tokens, function($token) {
            return mb_strlen($token, 'UTF-8') > 1;
        });
        
        // Rejoin
        return implode(' ', $tokens);
    }

    /**
     * Build person key from first and last name
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    public function buildPersonKey(string $firstName, string $lastName): string
    {
        $key = trim($firstName . ' ' . $lastName);
        return $this->normalizeText($key, false);
    }

    /**
     * Build index from File 1 data for efficient matching
     *
     * @param array $file1Data Array of ['row' => int, 'key' => string, 'original' => string]
     * @return array ['exact' => [], 'tokens' => []]
     */
    public function buildIndex(array $file1Data): array
    {
        $exactMap = [];
        $tokenMap = [];
        
        foreach ($file1Data as $item) {
            $normalizedKey = $item['key'];
            
            // Exact match map
            if (!isset($exactMap[$normalizedKey])) {
                $exactMap[$normalizedKey] = [];
            }
            $exactMap[$normalizedKey][] = $item;
            
            // Token map
            $tokens = array_unique(explode(' ', $normalizedKey));
            foreach ($tokens as $token) {
                if (strlen($token) >= 2) { // Only index tokens with 2+ chars
                    if (!isset($tokenMap[$token])) {
                        $tokenMap[$token] = [];
                    }
                    $tokenMap[$token][] = $item;
                }
            }
        }
        
        return [
            'exact' => $exactMap,
            'tokens' => $tokenMap
        ];
    }

    /**
     * Score a match between two strings
     *
     * @param string $a
     * @param string $b
     * @return array ['score' => int, 'type' => string]
     */
    public function scoreMatch(string $a, string $b): array
    {
        // Exact match
        if ($a === $b) {
            return ['score' => 100, 'type' => 'exact'];
        }
        
        // Contains match (if min length >= 5)
        if ((strlen($a) >= 5 || strlen($b) >= 5)) {
            if (strpos($a, $b) !== false || strpos($b, $a) !== false) {
                return ['score' => 92, 'type' => 'contains'];
            }
        }
        
        // Fuzzy match
        $tokensA = array_filter(explode(' ', $a));
        $tokensB = array_filter(explode(' ', $b));
        
        // Token overlap (Jaccard similarity)
        $intersection = count(array_intersect($tokensA, $tokensB));
        $union = count(array_unique(array_merge($tokensA, $tokensB)));
        $tokenScore = $union > 0 ? ($intersection / $union) * 100 : 0;
        
        // Levenshtein similarity
        $maxLen = max(strlen($a), strlen($b));
        if ($maxLen > 0) {
            $levenshtein = levenshtein($a, $b);
            $levenshteinScore = (1 - ($levenshtein / $maxLen)) * 100;
        } else {
            $levenshteinScore = 0;
        }
        
        // Take the maximum
        $finalScore = max($tokenScore, $levenshteinScore);
        
        return ['score' => (int)round($finalScore), 'type' => 'fuzzy'];
    }

    /**
     * Find best match for a File 2 key in the File 1 index
     *
     * @param string $file2Key
     * @param array $index
     * @param int $maxCandidates
     * @return array|null ['row' => int, 'original' => string, 'score' => int, 'type' => string]
     */
    public function findBestMatch(string $file2Key, array $index, int $maxCandidates = 300): ?array
    {
        // Try exact match first
        if (isset($index['exact'][$file2Key])) {
            $match = $index['exact'][$file2Key][0]; // Take first exact match
            return [
                'row' => $match['row'],
                'original' => $match['original'],
                'score' => 100,
                'type' => 'exact'
            ];
        }
        
        // Gather candidates from token map
        $tokens = array_unique(explode(' ', $file2Key));
        $candidatesMap = [];
        
        foreach ($tokens as $token) {
            if (isset($index['tokens'][$token])) {
                foreach ($index['tokens'][$token] as $candidate) {
                    $candidateKey = $candidate['key'];
                    if (!isset($candidatesMap[$candidateKey])) {
                        $candidatesMap[$candidateKey] = [
                            'item' => $candidate,
                            'sharedTokens' => 0
                        ];
                    }
                    $candidatesMap[$candidateKey]['sharedTokens']++;
                }
            }
        }
        
        // Sort by shared tokens and limit candidates
        uasort($candidatesMap, function($a, $b) {
            return $b['sharedTokens'] - $a['sharedTokens'];
        });
        
        $candidates = array_slice($candidatesMap, 0, $maxCandidates);
        
        // Score each candidate
        $bestMatch = null;
        $bestScore = 0;
        
        foreach ($candidates as $candidateData) {
            $candidate = $candidateData['item'];
            $result = $this->scoreMatch($file2Key, $candidate['key']);
            
            if ($result['score'] > $bestScore) {
                $bestScore = $result['score'];
                $bestMatch = [
                    'row' => $candidate['row'],
                    'original' => $candidate['original'],
                    'score' => $result['score'],
                    'type' => $result['type']
                ];
            }
        }
        
        return $bestMatch;
    }

    /**
     * Match two files
     *
     * @param array $file1Data Array of ['row' => int, 'key' => string, 'original' => string]
     * @param array $file2Data Array of ['row' => int, 'key' => string, 'original' => string]
     * @param string $mode 'business' or 'person'
     * @param int $threshold
     * @param bool $showPossible
     * @return array
     */
    public function matchFiles(array $file1Data, array $file2Data, string $mode, int $threshold, bool $showPossible): array
    {
        // Build index
        $index = $this->buildIndex($file1Data);
        
        $results = [];
        
        foreach ($file2Data as $file2Item) {
            $bestMatch = $this->findBestMatch($file2Item['key'], $index);
            
            $result = [
                'file2_row' => $file2Item['row'],
                'file2_original' => $file2Item['original'],
                'file2_normalized' => $file2Item['key'],
                'file1_row' => $bestMatch ? $bestMatch['row'] : null,
                'file1_original' => $bestMatch ? $bestMatch['original'] : null,
                'match_type' => $bestMatch ? $bestMatch['type'] : 'no match',
                'score' => $bestMatch ? $bestMatch['score'] : 0
            ];
            
            // Filter by threshold
            if ($showPossible) {
                // Show matches with score >= 70
                if ($result['score'] >= 70) {
                    $results[] = $result;
                }
            } else {
                // Show only matches >= threshold
                if ($result['score'] >= $threshold) {
                    $results[] = $result;
                }
            }
        }
        
        return $results;
    }
}
