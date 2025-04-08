<x-layout>
    <x-slot:header>
        Avatars
    </x-slot:header>
<div class="grid xs:grid-cols-2 gap-4 mt-3">
    @foreach ($avatars as $avatar)
    <div class="flex items-center justify-between">
    <img src="{{Vite::asset('\storage\app/public/'.$avatar->file_name)}}" alt="what"
        class="block px-1 py-1 border border-gray-600 rounded-lg" width="200" height="200">
        <a href="/avatar/{{$avatar->id}}" class="text-sm bg-red-900 rounded-md text-white ">delete</a>
    </div>
    @endforeach
</div>
    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Upload a new avatar</h1>
    <form action="/avatar" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="justify-between">
        <input type="file" name="avatar" value="avatar" id="avatar">
        <button type="submit" class="rounded-md bg-green-900 px-3 py-2 text-sm font-medium text-white">Save</button>
        </div>
    </form>
</x-layout>