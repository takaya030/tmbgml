<?php

namespace App\Http\Models\Tumblr;

/**
 * Tumblr text post
 */
class PostText extends PostBase
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
	}

	protected function getPostBody()
	{
	}
}
