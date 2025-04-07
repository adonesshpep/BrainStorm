<x-layout>
    <x-slot:header>{{$user->name}}</x-slot:header>
    <div class="p-6">
        <h2>info about the User </h2>
        <ul>
            <li>email: {{$user->email}}</li>
            <li>name: {{$user->name}}</li>
            <li>created at: {{$user->created_at}}</li>
            <li>status: {{($user->isadmin)?"Admin":"User"}}</li>
        </ul>
        <p class="mt-6">
            <button class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-black border border-gray-300
            rounded-md leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring ring-gray-300 focus:border-blue-300
            active:bg-black-100 active:text-white-500 transition ease-in-out duration-150 dark:bg-gray-800 dark:border-gray-600
            dark:active:bg-gray-700 dark:focus:border-blue-800" form="delete">Delete User</button>
        </p>
        <form method="POST" action="/dashboard/{{$user->id}}" id="delete">@csrf @method('DELETE')</form>
    </div>
</x-layout>