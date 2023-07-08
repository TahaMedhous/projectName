<!-- resources/views/top_albums.blade.php -->
<html>

<head>
    <title>Top 10 Albums</title>
</head>

<body>
    <h1>Top 10 Albums</h1>
    <ul>
        @foreach($albums as $album)
        <li>
            <a href="{{ route('album', ['id' => $album['id']]) }}">{{ $album['name'] }}</a>
        </li>
        @endforeach
    </ul>
</body>

</html>