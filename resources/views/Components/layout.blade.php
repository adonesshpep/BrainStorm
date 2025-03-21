<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brian Storm</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-black">
    <div class="px-10">
        <nav class="flex justify-between items-center bg-black-500 text-white py-4 border-b border-white/10">
            <div>
                <a href="">
                    <img src="{{Vite::asset('resources/images/logo.jpg')}}" alt="" sizes="250 400">
                </a>
            </div>
        </nav>
        <main class="mt-10 text-white max-w-[986px] mx-auto">{{$slot}}</main>
    </div>
</body>
</html>