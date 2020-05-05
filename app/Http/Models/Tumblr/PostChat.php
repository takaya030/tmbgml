<?php

namespace App\Http\Models\Tumblr;

/**
 * Tumblr chat post
 */
class PostChat extends PostBase
{
    /**
     * @param mixed $post_data [require] A Tumblr post.
     */
	public function __construct( $post_data )
	{
		parent::__construct( $post_data );
		$this->parse();
	}

	protected function parse()
	{
		$this->subject = $this->makeSubject( $this->data->type, $this->data->summary );
	}

	protected function getPostBody()
	{
		$body = "";

		if( !empty($this->data->body) )
		{
			$body .=  '<div>' .  str_replace( "\n", "<br />", $this->data->body ) . '</div><br />';
		}

		return $body;
	}
}
