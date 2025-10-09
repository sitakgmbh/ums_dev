<?php

namespace App\Livewire\Pages\Admin;

use Livewire\Component;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Changelog extends Component
{
    public array $entries = [];

    public function mount()
    {
        $file = base_path('CHANGELOG.md');

        if (File::exists($file)) {
            $markdown = File::get($file);

            preg_match_all(
                '/^##\s+\[(.*?)\]\s+–\s+(.*?)$(.*?)(?=^##\s+\[|\z)/ms',
                $markdown,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $index => $match) {
                $this->entries[] = [
                    'version' => trim($match[1]),
                    'date'    => trim($match[2]),
                    'body'    => $this->simpleMarkdown(trim($match[3])),
                ];
            }
        }
    }

    private function simpleMarkdown(string $text): string
    {
        // Headings
        $text = preg_replace('/^#### (.*)$/m', '<h4>$1</h4>', $text);
        $text = preg_replace('/^### (.*)$/m', '<h3>$1</h3>', $text);
        $text = preg_replace('/^## (.*)$/m', '<h2>$1</h2>', $text);
        $text = preg_replace('/^# (.*)$/m', '<h1>$1</h1>', $text);

        // Fett + kursiv
        $text = preg_replace('/\*\*\*(.*?)\*\*\*/s', '<strong><em>$1</em></strong>', $text);

        // Fett
        $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);

        // Kursiv
        $text = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $text);

        // Listen
        $text = preg_replace('/^- (.*)$/m', '<li>$1</li>', $text);
        $text = preg_replace('/(<li>.*<\/li>)/sU', '<ul>$1</ul>', $text);

        // Absätze
        $text = preg_replace("/\n{2,}/", "</p><p>", $text);
        $text = '<p>' . $text . '</p>';

        return $text;
    }

    public function render()
    {
        return view('livewire.pages.admin.changelog')
			->layout("layouts.app", [
				"pageTitle" => "Changelog",
			]);        
    }
}
