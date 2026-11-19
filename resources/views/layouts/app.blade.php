
<!DOCTYPE html>
<html lang="hu">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Magyar utónevek')</title>
    </head>
    <body>
        <header>
            <nav aria-label="Fő navigáció">
                <!-- <a href="{{ route('names.index') }}">Magyar utónevek</a> -->
                <a href="{{ route('names.index') }}">Névlista</a>
                <a href="{{ route('search.index') }}">Keresés</a>
            </nav>
        </header>
        <main>
            @yield('content')
        </main>
    </body>
</html>
