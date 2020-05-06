<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \App\Http\Models\Tumblr\PostFactory;
use \App\Http\Models\Google\Gmail;
use App\Http\Models\Google\Datastore;
use App\Http\Models\Google\Datastore\Entity;

class TumblrController extends Controller
{
	private $base_uri = 'https://api.tumblr.com';


	public function getPosts( Request $request )
	{
		$real_offset = -1;
		$real_limit = -1;

		$datastore = new Datastore();
		$entity = $datastore->lookup( env('DATASTORE_KIND'), env('TUMBLR_USER_ID') );
		if( $entity instanceof Entity )
		{
			// 前回終了時の offset
			$prev_offset = (int)$entity->get('start_offset');

			list( $real_offset, $real_limit ) = $this->getOffetAndLimit( $prev_offset );

			dd([$real_offset, $real_limit]);

			/*
			// 前回最後の次から
			if( $offset == 0 )
				$last_time_favorited++;
			else
			{
				// time_favorited でソートするため、同時刻にポストされた複数のアイテムはすべて読み込む
				$skip_offset = $offset + 1;
				$num_read_items += $offset;			// 読み込む個数をスキップする分増やす
			}
			 */
		}

		dd($start_offset);



		$client = new \GuzzleHttp\Client([
			'base_uri' => $this->base_uri,
		]);

		$num_mails = env('TUMBLR_MAX_POSTS');		// Gmail にインサートするアイテム数

		$response = $client->request('GET', '/v2/blog/'.env('TUMBLR_USER_ID').'.tumblr.com/posts', [ 'query' => [
			'api_key'		=> env('TUMBLR_API_KEY'),
			'limit'			=> $num_mails,
		]]);

		$response_body = (string)$response->getBody();
		$result = json_decode( $response_body );

		$post_obj = PostFactory::create( $result->response->posts[0] );
		if( is_null($post_obj) )
		{
			dd("Unknown type.");
		}

		$gmail = new Gmail();
		$gmail->insertMail( $post_obj );

		dd($post_obj->getPostData());
		//dd($result);
	}


	private function getOffetAndLimit( $prev_offset )
	{
		$real_offset = 0;
		$real_limit = 0;

		$client = new \GuzzleHttp\Client([
			'base_uri' => $this->base_uri,
		]);

		$response = $client->request('GET', '/v2/blog/'.env('TUMBLR_USER_ID').'.tumblr.com/info', [ 'query' => [
			'api_key'		=> env('TUMBLR_API_KEY'),
		]]);

		$response_body = (string)$response->getBody();
		$result = json_decode( $response_body );

		if( $result->meta->status == 200 && $result->meta->msg === "OK" )
		{
			$total_posts = (int)$result->response->blog->posts;			// 現在のポスト数
			$num_mails = (int)env('TUMBLR_MAX_POSTS');			// 1リクエストで処理するポスト数

			if( $total_posts <= $prev_offset + $num_mails )
			{
				// 先頭から処理する
				//$real_offset = 0;
				$real_limit = $total_posts - $prev_offset;
			}
			else
			{
				// 前回から num_mails 以上ポストされた
				$real_offset = ($total_posts - $prev_offset) - $num_mails;
				$real_limit = $num_mails;
			}
		}

		return [ $real_offset, $real_limit ];
	}
}
