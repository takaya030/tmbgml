<?php

namespace App\Http\Models\Google;

use \App\Http\Models\Google\Datastore\Entity;

class Datastore extends OAuthClient
{
	protected $list_labels = [];
	protected $base_url = '';

	public function __construct( bool $is_refresh_token = true )
	{
		parent::__construct( $is_refresh_token );

		$this->base_url = 'https://datastore.googleapis.com/v1/projects/' . env('GOOGLE_PROJECT_ID');
	}

	public function getAll()
	{
		$googleService = $this->getOauthService();

		$query = <<< EOM
{
	"gqlQuery":{
		"queryString": "select * from " . $env('DATASTORE_KIND') . " where nextindex = @1",
		"positionalBindings": [
			{"value": {"integerValue": 4032}}
		]
	}
}
EOM;
		// Send a request with it
		$result = json_decode(
			$googleService->request(
				$this->base_url . ':runQuery',
				'POST',
				$query,
				[ 'Content-type' => 'application/json' ]
			),
			true
		);

		return $result;
	}

	public function lookup( string $kind, string $name )
	{
		$googleService = $this->getOauthService();
		$projectId = env('GOOGLE_PROJECT_ID');

		$body = <<< EOM
{
	"keys":[
		{ "partitionId":{ "projectId":"{$projectId}"}, "path":[ {"kind":"{$kind}","name":"{$name}"} ] }
	 ]
}
EOM;
		// Send a request with it
		$result = json_decode(
			$googleService->request(
				$this->base_url . ':lookup',
				'POST',
				$body,
				[ 'Content-type' => 'application/json' ]
			),
			true
		);

		if( isset($result['found'][0]['entity']) )
		{
			$entity = new Entity( $result['found'][0]['entity'], $result['found'][0]['version'] );
		}
		else
		{
			$entity = $result;
		}

		return $entity;
	}

	public function upsert( string $kind, string $name, array $properties )
	{
		$googleService = $this->getOauthService();
		$projectId = env('GOOGLE_PROJECT_ID');
		$params_str= "";

		foreach( $properties as $key => $value )
		{
			if( empty($params_str) )
				$params_str = static::getPropertyString( $key, $value );
			else
				$params_str .= ',' . static::getPropertyString( $key, $value );
		}

		$body = <<< EOM
{
	"mode":"NON_TRANSACTIONAL",
	"mutations":[
		{
			"upsert": {
				"key":{ "partitionId":{ "projectId":"{$projectId}" }, "path":[ {"kind":"{$kind}","name":"{$name}"} ] },
				"properties": {
					{$params_str}
				}
			}
		}
	 ]
}
EOM;
		// Send a request with it
		$result = json_decode(
			$googleService->request(
				$this->base_url . ':commit',
				'POST',
				$body,
				[ 'Content-type' => 'application/json' ]
			),
			true
		);

		return $result;
	}

	static protected function getPropertyString( $key, $value )
	{
		$tyep_str = 'stringValue';

		if( is_numeric($value) )
			$type_str = 'integerValue';
		if( is_null($value) )
			$tyep_str = 'nullValue';

		return "\"{$key}\":{\"{$type_str}\":\"{$value}\"}";
	}
}
