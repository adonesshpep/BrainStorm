<x-layout>
    <x-slot:header>
        Dashboard
    </x-slot:header>
    <div class="space-y-4">
        @foreach ($users as $user)
        <a href="/dashboard/{{$user->id}}" class="block px-4 py-6 border border-gray-600 rounded-lg">
            <div class="font-bold text-blue-500 text-sm">{{$user->name}}</div>
            <div>
                email : {{$user->email}}
            </div>
        </a>
        @endforeach
    </div>
</x-layout>