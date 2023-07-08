<!-- resources/views/album.blade.php -->
<html>

<head>
    <title>Album Details</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script type="text/javascript" src="{{asset('js/app.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
            user-select: none;
        }

        @media (max-width: 640px) {
            .album-details {
                text-align: center;
            }
        }

        .trackName {
            max-width: 35rem;
        }

        @media screen and (max-width: 768px) {
            .trackName {
                max-width: 25rem;
            }
        }

        @media screen and (max-width: 640px) {
            .trackName {
                max-width: 23rem;
            }
        }


        @media screen and (max-width: 620px) {
            .trackName {
                max-width: 19rem;
            }
        }

        @media screen and (max-width: 540px) {
            .trackName {
                max-width: 15rem;
            }
        }


        @media screen and (max-width: 480px) {
            .trackName {
                max-width: 8rem;
            }
        }

        @media screen and (max-width: 320px) {
            .trackName {
                max-width: 50px;
            }
        }

        #songN:nth-child(odd) {
            background-color: #2d2d2d42;
        }

        #songN:nth-child(odd):hover {
            background-color: #0a0a0a;
        }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

</head>

<body class="bg-zinc-900">
    <div class="p-10">
        @if($albumData)
        <div class="album-details flex flex-col text-center md:flex-row md:text-left items-center justify-start">
            <div class="w-64 h-64">
                <img src="{{ $albumData['artworkUrl1000'] }}" alt="Album Cover" class="album-image rounded-md object-cover w-[16.5rem] h-[16.5rem] blur-[14px] top-[-0.25rem] relative opacity-75">
                <img src="{{ $albumData['artworkUrl1000'] }}" alt="Album Cover" class="album-image rounded-md object-cover w-64 h-64 relative top-[-16.5rem] border-[1px] border-[#ffffff1c]">
            </div>
            <div class="ml-0 md:ml-10 mt-10 md:mt-0 flex flex-col items-center md:items-start gap-4">
                <h1 class="text-2xl font-bold text-white">
                    {{ $albumData['collectionName'] }}
                </h1>
                <a class="text-xl font-bold text-white" href="{{ $albumData['artistViewUrl'] }}">{{ $albumData['artistName'] }}</a>
                <h3 class="text-xl font-bold text-white">
                    {{ $albumData['primaryGenreName'] }} &middot; {{ date('Y',
    strtotime($albumData['releaseDate'])) }}
                </h3>
                @if($albumData['collectionExplicitness'] == 'explicit')
                <img src="{{ asset('icons/explicit.svg') }}" alt="Explicit" class="w-6 h-6" />
                @endif

                <a class="text-base font-bold inline-block px-4 py-2 bg-neutral-600 text-white rounded-md hover:bg-neutral-500 transition-colors flex items-center cursor-pointer" onclick="playAudioMaster( '{{ $albumData['songs'][0]['previewUrl'] }}', 'audioPlayer', 'playIconNumber{{ $albumData['songs'][0]['trackNumber'] }}')">
                    Preview</a>
            </div>
        </div>
        <hr class="mt-2 opacity-20 md:mt-10 md:mb-5">
        @foreach($albumData['songs'] as $song)
        <div class="song flex flex-row items-center justify-between py-4 px-2 bg-zinc-800 hover:bg-[#0a0a0a] transition-colors border border-zinc-700 rounded-md mt-2" id="songN">
            <div class="flex items-center gap-4">
                <div class="text-sm text-white opacity-50">
                    {{ $song['trackNumber'] }}
                </div>
                <div class="flex flex-col trackName">
                    <h1 class="text-base text-white truncate">{{ $song['trackName'] }}</h1>
                </div>
            </div>
            <div id="play" class="flex items-center gap-4">
                <a target="_blank" class="text-base font-bold inline-block px-4 py-2 bg-neutral-600 text-white rounded-md hover:bg-neutral-500 transition-colors flex items-center cursor-pointer w-24" onclick="playAudioMaster( '{{ $song['previewUrl'] }}', 'audioPlayer', 'playIconNumber{{ $song['trackNumber'] }}')">
                    <i class="fa-solid fa-play inline-block w-4 h-4 mr-2" style="color: #ffffff;" id="playIconNumber{{ $song['trackNumber'] }}"></i>
                    Play</a>
            </div>
        </div>
        @endforeach
        <div class="fixed bottom-0 left-0 w-full">

            <audio src="{{ $albumData['previewUrl'] }}" controls id="audioPlayer" class="w-full bg-zinc-900 sm:w-1/2 md:w-1/3 lg:w-1/4 xl:w-1/5 mx-auto rounded-t-md px-2 py-2 drop-shadow-md" hidden>
            </audio>
        </div>
        @else
        <div class="text-center">
            <h1 class="text-4xl font-bold">Album Not Found</h1>
            <a href="/" class="inline-block mt-4 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Go Back</a>
        </div>
        @endif
    </div>
</body>