<?php

/*
IF YOU NEED TO DEBUG QUERIES 

<script type="text/javascript">
	var queries = {{ json_encode(DB::getQueryLog()) }};
	console.log('/------------------------------ Database Queries ------------------------------/');
	console.log(' ');
	queries.forEach(function(query) {
		console.log('   ' + query.time + ' | ' + query.query + ' | ' + query.bindings[0]);
	});
	console.log(' ');
	console.log('/------------------------------ End Queries -----------------------------------/');
</script>
*/

// Index page is a list of all talks
Route::get('/', function() {

	$talks = Talk::where('status','=','approved')->
		where('date_start', '>=', new DateTime('today'))->
		orderBy('updated_at', 'asc')->paginate(5);

	return View::make('home')
		->with('talks', $talks);
});

// Talk description view
Route::get('talk/{id}',  function($id) {

	$talk = Talk::findOrFail($id);

	$name_list = DB::select('select name from users 
		inner join speakers on speakers.user_id = users.id
		where speakers.talk_id = ?', array($talk->id));
	$speaker_names = $name_list[0];
	return View::make('talk_view')
		->with('talk', $talk)
		->with('speaker_names', $speaker_names);
}) ;


// When a user is logged in he/she is taken to creating new post
Route::get('talk_new', array('before' => 'auth', 'do' => function() {
	$user = Auth::user();
	$talksUser = User::all();

	$name_list = array();
	foreach ($talksUser as $u) {
		$name_list[$u->id] = $u->name;
	}

	return View::make('talk_new')->with('user', $user)->
		with('name_list',$name_list);
}));

/**
Route::get('post/(:num)', array('before' => 'auth', 'do' => function($id){

	$user = Auth::user();
	$view_post = Post::with('user')->find($id);
	return View::make('edit')
			->with('user', $user)
			->with('post', $view_post);
})) ;

Route::put('post/(:num)', array('before' => 'auth', 'do' => function($id){
	
	$post_title = Input::get('post_title');
	$post_body = Input::get('post_body');
	$post_author = Input::get('post_author');
	$edit_post = array(
		'post_title'	=> $post_title,
		'post_body'	 => $post_body,
		'post_author'   => $post_author
	);
   
	$rules = array(
		'post_title'	 => 'required|min:3|max:255',
		'post_body'	  => 'required|min:10'
	);
	
	$validation = Validator::make($edit_post, $rules);
	if ( $validation -> fails() )
	{
		
		return Redirect::to('post/'.$id)
				->with('user', Auth::user())
				->with_errors($validation)
				->with_input();
	}
	// save the post after passing validation
	$post = Post::with('user')->find($id);
	$post->post_title = $post_title;
	$post->post_body = $post_body;
	$post->post_author = $post_author;
	$post->save();
	// redirect to viewing all posts
	return Redirect::to('/')->with('success_message', true);

})) ;


Route::delete('post/(:num)', array('before' => 'auth', 'do' => function($id){
	$delete_post = Post::with('user')->find($id);
	$delete_post -> delete();
	return Redirect::to('/')
			->with('success_message', true);
})) ;
*/



// When the new post is submitted we handle that here
Route::post('talk_new', array('before' => 'auth', 'do' => function() {
	$user = User::findOrFail( Auth::user()->id);
	if ($user->rights != 'admin') {
		Session::flash('error', 'unauthorized');
		Redirect::to('/');
		die();
	}

	$new_talk = array(
		'creator_id'	=> $user->id,	
		'title'		=> Input::get('title'),
		'target'	=> Input::get('target'),
		'aim'		=> Input::get('aim'),
		'requirements'	=> Input::get('reqs'),
		'description'	=> Input::get('desc'),
		'date_start'	=> Input::get('date_start'),
		'date_end'	=> Input::get('date_end'),
		'places'	=> Input::get('places'),
		'location'	=> Input::get('location'),
	);

	

	$rules = array(
		'title'		=> 'required|min:3|max:255',
		'speakers'	=> 'required|min:10',
	);
	/*
	$validation = Validator::make($new_post, $rules);
	if ( $validation -> fails() )
	{
		
		return Redirect::to('admin')
				->with('user', Auth::user())
				->with_errors($validation)
				->with_input();
	}
	*/
/*
	// create the new talk after passing validation
	$talk = new Talk();
	$talk->creator_id	= $user->id;	
	$talk->title		= Input::get('title');
	$talk->target		= Input::get('target');
	$talk->aim		= Input::get('aim');
	$talk->requirements	= Input::get('reqs');
	$talk->description	= Input::get('desc');
	$talk->date_start	= Input::get('date_start');
	$talk->date_end		= Input::get('date_end');
	$talk->places		= Input::get('places');
	$talk->location		= Input::get('location');

	$talk->save();
	// now that we have the talk go on with the spakers
	foreach (Input::get('speakers') as $speaker_id) {
		$speaker = new Speaker();
		$speaker->user_id = $speaker_id;
		$speaker->talk_id = $talk->id;
		$speaker->save();
	}
*/
	// redirect to viewing all posts
	return Redirect::to('/');
	//var_dump(Input::get('speakers'));
}));


// Present the user with login form
Route::get('login', function() {
	return View::make('login');
});


// Process the login
Route::post('login', function() {
	// TODO: Rewrite login to use LDAP!
	//var_dump(debug_backtrace());
	$userinfo = array(
		'username' => Input::get('username'),
		'password' => Input::get('password')
	);
	$attempt = Auth::attempt($userinfo);
	if ( $attempt )
	{
		return Redirect::to('/');
	}
	else
	{
		return Redirect::to('login')
			->with('login_errors', true);
	}
});


// Process Logout process
Route::get('logout', function() {
	Auth::logout();
	return Redirect::to('/')
		->with('logout_message', true);
});


/*
|--------------------------------------------------------------------------
| Application 404 & 500 Error Handlers
|--------------------------------------------------------------------------
|
| To centralize and simplify 404 handling, Laravel uses an awesome event
| system to retrieve the response. Feel free to modify this function to
| your tastes and the needs of your application.
|
| Similarly, we use an event to handle the display of 500 level errors
| within the application. These errors are fired when there is an
| uncaught exception thrown in the application.
|
*/

Event::listen('404', function()
{
	return Response::error('404');
});

Event::listen('500', function()
{
	return Response::error('500');
});

/*
|--------------------------------------------------------------------------
| Route Filters
|--------------------------------------------------------------------------
|
| Filters provide a convenient method for attaching functionality to your
| routes. The built-in before and after filters are called before and
| after every request to your application, and you may even create
| other filters that can be attached to individual routes.
|
| Let's walk through an example...
|
| First, define a filter:
|
|		Route::filter('filter', function()
|		{
|			return 'Filtered!';
|		});
|
| Next, attach the filter to a route:
|
|		Router::register('GET /', array('before' => 'filter', function()
|		{
|			return 'Hello World!';
|		}));
|
*/

Route::filter('before', function()
{
	// Do stuff before every request to your application...
});

Route::filter('after', function($response)
{
	// Do stuff after every request to your application...
});

Route::filter('csrf', function()
{
	if (Request::forged()) return Response::error('500');
});

Route::filter('auth', function()
{
	if (Auth::guest()) return Redirect::to('login');
});

