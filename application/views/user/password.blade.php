@include('head')

<h2>Zabudnuté heslo</h2>


{{ Form::open(null, 'POST', array('class' => 'side-by-side')); }}

@if (isset($error) && $error == true)
	<div class="alert alert-error">{{ $error }}</div>
@endif

<div {{ isset($errors->password) ? ' class="control-group error"' : '' }}>
	{{ Form::label('password', 'Nové heslo', array('class' => 'control-label')) }}
	{{ Form::password('password') }}
	{{ isset($errors->password) ? '<span class="help-inline">'.$errors['password'].'</span>' : '' }}
</div>

<div {{ isset($errors->password_repeat) ? ' class="control-group error"' : '' }}>
	{{ Form::label('password_repeat', 'Potvrď heslo', array('class' => 'control-label')) }}
	{{ Form::password('password_repeat') }}
	{{ isset($errors->password_repeat) ? '<span class="help-inline">'.$errors['password_repeat'].'</span>' : '' }}
</div>

<div class="submit">
	{{ Form::submit('Zmeniť heslo') }}
</div>


{{ Form::close() }}


@include('foot');