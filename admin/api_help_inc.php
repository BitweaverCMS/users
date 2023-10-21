<?php

global $gApiHelp, $gBitUser, $gBitSystem;

$gApiHelp['User Registration'] = array( 
	'Request Methods' => array(
		'method' => 'POST '.API_PKG_URI.'users/register',
		'help' => '',
		'parameters' => array(
			'POST '.API_PKG_URI.'users' => 'POST can be used for creation *and* updating existing user objects',
			'PUT '.API_PKG_URI.'users' => 'A PUT will perform identically to POST',
			'GET '.API_PKG_URI.'users' => 'Once authenticated, and GET to this URL will return JSON of the active user object',
		),
	),
	'Sendable Properties' => array(
		'help' => 'These are mutable parameters can be sent to the host to store information about the user',
		'parameters' => array( 
            'email' => 'valid email, * required for registration.',
            'password' => 'Password to be saved with account. Will be salted and hashed and is irrecoverable. * required for registration',
            'login' => 'username consisting of alphanumeric letters, optional for registration, though a default will be assigned if not set',
            'real_name' => 'The user\'s full name, including first and last names, optional',
		),
	),
	'Examples' => array( 
		'help' => '',
		'code' => '<code>COMMAND:
echo -n \'{"email":"test@example.com","password":"s3cr3t"}\' |lwp-request -se -C test:foobar -c "application/json" -H \'API: API consumer_key="bad6ed95edfd983c8cb58cd397a242a2f83cd80c"\' -m PUT '.API_PKG_URI.'users/info

RESPONSE:
200 OK
Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
Connection: close
Date: Fri, 20 Jul 2012 01:36:13 GMT
Pragma: no-cache
Server: Apache/2.2.3 (CentOS)
Set-Cookie: BWSESSION=2ishv84g637mrp7o07hd8829f6; path='.$gBitSystem->getConfig( 'cookie_path', '/' ).'; domain='.$gBitSystem->getConfig( 'cookie_domain' ).'
Set-Cookie: <strong>'.$gBitUser->getSiteCookieName().'=2ishv84g637mrp7o07hd8829f6M</strong>; path=/
</code>',
	),
);

$gApiHelp['User Authentication'] = array( 
	'Authentication' => array(
		'method' => 'POST '.API_PKG_URI.'users/autheticate',
		'help' => 'Authentication is performed once per session (application launch) using the standard <a href="http://en.wikipedia.org/wiki/Basic_access_authentication">HTTP Basic Authentication</a>. Once authentication is successful, a cookie named "'.$gBitUser->getSiteCookieName().'" will be returned for the user. Those cookies will need to be included for every request which will automatically identify the user for the lifetime of the cookie.',
		'parameters' => array(
			'Authorization: Basic <em>base64encode(username + ":" + password)</em>' => 'HTTP Basic authenictaion sent via HTTP headers. Most frameworks will handle this for you automatically with a simple call. For example AFNetworking <a href=http://engineering.gowalla.com/AFNetworking/Classes/AFHTTPClient.html">::setAuthorizationHeaderWithUsername</a>',
		),
	),
	'Receivable Properties' => array(
		'help' => 'In addition to the sendable properties listed above, these parameters will be sent by the host with information about the user.',
		'parameters' => array( 
            'last_login' => '',
            'current_login' => '',
            'registration_date' => '',
            'is_registered' => '',
            'portrait_url' => '',
            'avatar_url' => '',
            'logo_url' => '',
            'email' => '',
            'login' => '',
            'real_name' => '',
            'user' => '',
		),
	),
	'Examples' => array(
		'help' => 'The following is a command-line test of user authentication. Notice the Set-Cookie values returned. Your client should store these values and send them for all subsequent requests.',
		'code' => '<code>COMMAND:
lwp-request -se -C test@example.com:s3cr3t -H \'API: API consumer_key="bad6ed95edfd983c8cb58cd397a242a2f83cd80c"\' -m GET '.API_PKG_URI.'users/info

RESPONSE:
200 OK
Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
Connection: close
Date: Thu, 19 Jul 2012 21:50:06 GMT
Pragma: no-cache
Server: Apache/2.2.3 (CentOS)
Content-Length: 741
Content-Type: application/json
Expires: Thu, 19 Nov 1981 08:52:00 GMT
Access-Control-Allow-Origin: *
Client-Date: Thu, 19 Jul 2012 21:50:07 GMT
Client-Peer: 66.179.240.119:443
Client-Response-Num: 1
Set-Cookie: BWSESSION=2ishv84g637mrp7o07hd8829f6; path='.$gBitSystem->getConfig( 'cookie_path', '/' ).'; domain='.$gBitSystem->getConfig( 'cookie_domain' ).'
Set-Cookie: <strong>'.$gBitUser->getSiteCookieName().'=2ishv84g637mrp7o07hd8829f6M</strong>; path=/
X-Powered-By: PHP/5.3.1

{"user_id":"1002","content_id":"1043","email":"text@example.com","login":"test","real_name":"Test User","provpass":null,"provpass_expires":null,"default_group_id":null,"last_login":"1342734606","current_login":"1342734606","registration_date":"1341806036","challenge":null,"pass_due":"1429048188","created":null,"avatar_attachment_id":null,"portrait_attachment_id":null,"logo_attachment_id":null,"avatar_file_name":null,"avatar_mime_type":null,"portrait_file_name":null,"portrait_mime_type":null,"logo_file_name":null,"logo_mime_type":null,"uu_user_id":"1002","user":"text","valid":true,"is_registered":true,"portrait_path":null,"portrait_url":null,"avatar_path":null,"avatar_url":null,"logo_path":null,"logo_url":null,"first_name":"Test User"}</code>',
	), 
);
