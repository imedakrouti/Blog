<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'importDate' => $this->importDate,
            'rawContent' => $this->rawContent,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'articles' => ArticleResource::collection($this->whenLoaded('import'))
        ];
    }
}
