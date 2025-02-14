<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Projeto Bonito com Laravel e Vue</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 text-gray-800">
    <header class="bg-white shadow">
        <div class="container mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold">Meu Projeto Incrível</h1>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8">
        <div id="app">
            <example-component></example-component>
        </div>
    </main>

    <footer class="bg-white shadow mt-8">
        <div class="container mx-auto px-4 py-4 text-center">
            <p>&copy; {{ date('Y') }} Meu Projeto Incrível. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>

</html>