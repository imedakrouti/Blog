<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;

use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Models\Import;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ImportController extends BaseController
{
    public function import(ImportRequest $request)
    {
        $rss = $request->siteRssUrl;
        // Download the RSS feed
        $import = $this->importFromUrl($rss);
        // Parse the XML
        $xml = simplexml_load_string($import->rawContent);
        $articles = $xml->channel->item;
        $namespaces = $xml->getNamespaces(true); // get namespaces

        foreach ($articles as $article) {
            $articleData = [
                'externalId' => $article->guid,
                'importDate' => $import->importDate,
                'title' => $article->title,
                'description' => $article->description,
                'publicationDate' =>  Carbon::createFromFormat('D, d M Y H:i:s O', $article->pubDate),
                'link' =>  $article->link,
                'mainPicture' => (string) $article->enclosure['url'],
                'import_id' => $import->id
            ];
            Article::firstOrNew(['externalId' => $articleData['externalId']], $articleData)->save();
        }
        $articles = Article::with('import')->get();
        $data['Article'] =  ArticleResource::collection($articles);
        return $this->sendResponse($data, 'list of roles');
    }

    public function importFromUrl($rss)
    {
        $import = Import::create([
            'importDate' => now(),
            'rawContent' => Http::get($rss)->body(),
        ]);
        return $import;
    }
}
