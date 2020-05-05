<?php

namespace App\Http\Models\Tumblr;

use \Carbon\Carbon;

/**
 * Tumblr post
 */
abstract class PostBase
{
	protected $data = null;
	protected $subject;			// mail subject
	protected $timestamp;		// rfc2822 string
	protected $tags = [];


    /**
     * @param mixed $post_data [require] A Tumblr post.
     */
	public function __construct( $post_data )
	{
		$this->data = $post_data;
		$this->parse_common();
	}

	private function parse_common()
	{
		$this->timestamp = Carbon::createFromTimestamp($this->data->timestamp, 'Asia/Tokyo')->toRfc2822String();

		if( !empty($this->data->tags) )
		{
			foreach( $this->data->tags as $tag )
			{
				$this->tags[] = $tag;
			}
		}
	}

	protected function makeSubject( string $ptype, string $summary )
	{
		$subject = isset( $summary ) ?  $summary : 'No title';
		if( empty($subject) )
			$subject = 'No title';

		$subject = (mb_strlen($subject,'utf-8') > 32)? 
			mb_substr($subject, 0, 32) . '...' :
			$subject;


		$rtype_str = 'Unknown';

		if( $ptype === 'quote' )
		{
			$type_str = 'Quote';
		}
		elseif( $ptype === 'text' )
		{
			$type_str = 'Text';
		}
		elseif( $ptype === 'link' )
		{
			$type_str = 'Link';
		}
		elseif( $ptype === 'chat' )
		{
			$type_str = 'Chat';
		}

		return '[' . $type_str . ']' . $subject;
	}


	public function getPostData()
	{
		return 'Date: '. $this->timestamp ."\r\n" .
			'From: '. env('TUMBLR_FROM_USER') ."\r\n" .
			'To: '. env('TUMBLR_TO_USER') ."\r\n" .
			'Subject: '. $this->makeEncodedSubject() ."\r\n" .
			"Content-Type: text/html; charset=UTF-8\r\n" .
			"\r\n" .
			'<html><body>'. $this->getMailBody() .'</body></html>';
	}

	private function makeEncodedSubject()
	{
		return '=?utf-8?B?' . base64_encode($this->subject) . '?=';
	}

	private function getMailBody()
	{
		return $this->getPostBody() .
			$this->getTagsStr() .
			$this->getPermaLink();
	}

	private function getPermaLink()
	{
		return '<div><a href="' . $this->data->post_url . '">Permalink</a></div><br />';
	}

	private function getTagsStr()
	{
		return ( count( $this->tags ) > 0 )?  '<div>tags: ' . implode( ',', $this->tags ) . '</div><br />' : "";
	}


	// custom parser for derived class
	abstract protected function parse();
	abstract protected function getPostBody();
}
