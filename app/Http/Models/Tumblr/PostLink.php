<?php

namespace App\Http\Models\Tumblr;

/**
 * Tumblr link post
 */
class PostLink extends PostBase
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
		$this->subject = $this->makeSubject( $this->data->type, $this->data->title );
	}

	protected function getTypeStr()
	{
		return 'Link';
	}

	protected function getPostBody()
	{
		$body = "";

		if( !empty($this->data->source_url) && !empty($this->data->title) )
		{
			$body .= '<div><a href="' . $this->data->source_url . '">' . $this->data->title . '</a></div><br />';
		}

		if( !empty($this->data->description) )
		{
			$body .= '<div>' . $this->data->description . '</div><br />';
		}
		elseif( !empty($this->data->excerpt) )
		{
			$body .= '<div>' . $this->data->excerpt . '</div><br />';
		}

		return $body;
	}
}
