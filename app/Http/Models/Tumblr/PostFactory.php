<?php

namespace App\Http\Models\Tumblr;

/**
 * Tumblr post factory
 */
class PostFactory
{
    /**
     * @param mixed $post_data [require] A Tumblr post.
	 * @return PostText|PostQuote|PostLink|PostChat|null
     */
	public static function create( $post_data )
	{
		$ptype = $post_data->type;

		if( $ptype === 'quote' )
		{
			return new PostQuote( $post_data );
		}
		elseif( $ptype === 'text' )
		{
			return new PostText( $post_data );
		}
		elseif( $ptype === 'link' )
		{
			return new PostLink( $post_data );
		}
		elseif( $ptype === 'chat' )
		{
			return new PostChat( $post_data );
		}

		// when unknown type
		return null;
	}
}
