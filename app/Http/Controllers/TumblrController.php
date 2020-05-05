<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \App\Http\Models\Tumblr\PostText;

class TumblrController extends Controller
{
	public function getPosts( Request $request )
	{
		$base_uri = 'https://api.tumblr.com';
		$client = new \GuzzleHttp\Client([
			'base_uri' => $base_uri,
		]);

		$num_mails = env('TUMBLR_MAX_POSTS');		// Gmail にインサートするアイテム数

		$response = $client->request('GET', '/v2/blog/'.env('TUMBLR_USER_ID').'.tumblr.com/posts', [ 'query' => [
			'api_key'		=> env('TUMBLR_API_KEY'),
			'limit'			=> $num_mails,
		]]);

		$response_body = (string)$response->getBody();
		$result = json_decode( $response_body );

		$post_text = new PostText( $result->response->posts[0] );
		dd($post_text);

		//dd($result);
	}
}
