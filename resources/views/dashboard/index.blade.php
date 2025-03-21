<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body>
    <div class="min-h-full">
        <nav class="bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="hidden md:block">
                            <div class="ml-10 flex items-baseline space-x-4">
                                <!-- Current: "bg-gray-900 text-white", Default: "text-gray-300 hover:bg-gray-700 hover:text-white" -->
                                <a href="#" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white" aria-current="page">Dashboard</a>
                                <a href="#" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white" aria-current="page">Profile</a>
                                <a href="#" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white" aria-current="page">Settings</a>
                                <button form="logout" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white" aria-current="page">Log Out</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>
    
        <header class="bg-white shadow-sm">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">Dashboard</h1>
            </div>
        </header>
        <main>
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <x-slot:head>
                        Users
                    </x-slot:head>
                    <div class="space-y-4">
                        @foreach ($users as $user)
                            <div>
                                name={{$user->name}} : email={{$user->email}}
                            </div>
                        @endforeach
                    </div>
            </div>
        </main>
    </div>
    <form method="POST" action="dashboard/logout" class="hidden" id="logout">@csrf</form>
</body>
</html>