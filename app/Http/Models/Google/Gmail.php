<?php

namespace App\Http\Models\Google;

use App\Http\Models\Tumblr\PostBase as TumblrPostItem;

class Gmail extends OAuthClient
{
	protected $list_labels = [];

	public function __construct( bool $is_refresh_token = true )
	{
		parent::__construct( $is_refresh_token );

		$this->list_labels = $this->getLabels();
	}

	protected function getLabels()
	{
		$googleService = $this->getOauthService();

		// Send a request with it
		$result = json_decode($googleService->request('https://www.googleapis.com/gmail/v1/users/me/labels','GET'), true);

		return (isset($result['labels']))? $result['labels'] : [];
	}

	public function insertMail( TumblrPostItem $item )
	{
		$raw = static::base64url_encode( $item->getPostData() );
		$label_id = $this->findLabelId( env('GMAIL_BASE_LABEL') );
		$params = [
			'raw'		=> $raw,
			'labelIds'	=> [ $label_id ],
		];

		$googleService = $this->getOauthService();

		// insert mail
		try {
			$result = json_decode(
				$googleService->request(
					'https://www.googleapis.com/gmail/v1/users/me/messages',
					'POST',
					json_encode($params),
					[ 'Content-type' => 'application/json' ]
				),
				true
			);
			sleep(1);

			return isset($result['id']);
		}
		catch(Exception $e)
		{
			//return 'An error occurred: ' . $e->getMessage();
		}

		return false;
	}

	protected static function base64url_encode( $data )
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	protected function findLabelId( $label_name )
	{
		if (count($this->list_labels) > 0) {
			foreach ($this->list_labels as $label) {
				if( $label['name'] == $label_name )
				{
					return $label['id'];
				}
			}
		}

		return 'INBOX';
	}
}
