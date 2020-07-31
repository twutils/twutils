<?php

namespace App\Http\Controllers;

use App\Issue;
use ParsedownExtra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class WelcomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function contact()
    {
        return view('contact');
    }

    public function storeContact(Request $request)
    {
        $this->validate($request, [
            'name'    => 'required|string|max:251',
            'email'   => 'required|string|email|max:251',
            'message' => 'required|string',
            'purpose' => 'required|string|max:251',
        ]);

        Issue::create([
            'name'    => $request->name,
            'email'   => $request->email,
            'message' => $request->message,
            'purpose' => $request->purpose,
        ]);

        return [
            'ok' => true,
        ];
    }

    public function about()
    {
        $content = $this->getMarkdownContent('about');

        return view('markdown', ['content' => $content]);
    }

    public function privacy()
    {
        $content = $this->getMarkdownContent('privacy');

        return view('markdown', ['content' => $content]);
    }

    protected function getMarkdownContent($path)
    {
        $locale = strtolower(app()->getLocale());
        $fileContent = File::get(resource_path("md/{$path}.{$locale}.md"));

        return (new ParsedownExtra())->text($fileContent);
    }
}
