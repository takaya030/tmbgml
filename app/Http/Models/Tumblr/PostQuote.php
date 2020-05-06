<?php

namespace App\Http\Models\Tumblr;

/**
 * Tumblr quote post
 */
class PostQuote extends PostBase
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

	protected function getTypeStr()
	{
		return 'Quote';
	}

	protected function getPostBody()
	{
		$body = "";

		if( !empty($this->data->text) )
		{
			$body .= '<div>' . $this->data->text . '</div><br />';
		}

		if( !empty($this->data->source) )
		{
			$body .= '<div>- ' . $this->data->source . '</div><br />';
		}

		return $body;
	}
}
