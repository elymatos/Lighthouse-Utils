<?php

namespace DeInternetJongens\LighthouseUtils\Client;

class GraphQLClient
{

    protected $key = 'graphql';
    protected $host = false;
    protected $token = false;
    protected $minutes = false;

    /**
     * Constructor of the GraphQL Client
     *
     * @param  string  $host
     * @param  string  $token
     * @return void
     */
    public function __construct( $host=null, $token=false )
    {
        $this->host = ( $host ?? config('lighthouse-utils.client.host') );
        $this->token = $token;
        $this->minutes = config('lighthouse-utils.client.cache');
    }

    /**
     * Set the Authentication token
     *
     * @param  string  $token
     * @return void
     */
    public function setToken( $token )
    {
        $this->token = $token;
    }

    /**
     * Make the query response
     *
     * @param  string  $query
     * @param  array  $params
     * @param  integer $cache
     * @return GraphQLResponse
     */
    public function query( $query, $params=false, $cache=null )
    {
        
        // query in cache?
		if( $data = $this->hasCache( $query ) )
		{
			return new GraphQLResponse( $data, $query );
        }
        
        
        // reset errors
        $this->errors = false;

        // curl to host
        $ch = curl_init( $this->host );


        if( !is_array($params) )
        {
            $params = [];
        }
        
        // attach token to the params
        if( $this->token )
        {
            $params['token'] = $this->token;
        }

        $params = array_merge($params, ['query' => $query]);
        
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $r = @curl_exec($ch);
        curl_close($ch);
        $data = json_decode($r);

        // response without cache
        if( !isset($cache) )
        {
            return new GraphQLResponse( $data, $query );
        }
        
        $cache = ( is_numeric($cache) ? $cache : $this->minutes );

        return new GraphQLResponse( $data, $query, $cache );


    }

    /**
     * Validate if this query is already in the cache
     *
     * @param  string  $query
     * @return object
     */
    public function hasCache( $query )
    {
        $key = md5($query);

        if( $data = cache($key) )
        {
            return $data;
        }

        return false;
    }

    


}