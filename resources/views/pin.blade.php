<!DOCTYPE html>
<html>
<head>
	<title>TwUtils (Beta) - PIN Required</title>
</head>
<body>
<div>
	<form style="width: 100%; height: 400px; display: flex; justify-content: center; align-items: center; flex-direction: column;" action="{{route('welcome')}}" method="post">
		@csrf
		<strong style="padding: 1rem; margin: 1rem; font-size:1rem; text-align: center;">Enter PIN CODE</strong>
		<input autocomplete="off" style="padding: 1rem; margin: 1rem; font-size:1rem; text-align: center;" autofocus="autofocus" type="text" name="pin">
		<button style="padding: 1rem; margin: 1rem; font-size:1rem; text-align: center;" type="submit">Validate..</button>
	</form>
</div>
</body>
</html>