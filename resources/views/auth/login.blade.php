<x-layout>
    <x-slot:header>
        Login
    </x-slot:header>
    <form method="POST" action="/login" enctype="multipart/form-data">
        @csrf
        <div class="space-y-12 p-6">
            <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <x-form-field>
                    <x-form-label for='email'>Email Address</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="email" id="email" type='email' required />
                        <x-form-error name='email' />
                    </div>
                </x-form-field>
                <x-form-field>
                    <x-form-label for='password'>Password</x-form-label>
                    <div class="mt-2">
                        <x-form-input name="password" id="password" type='password' required />
                        <x-form-error name='password' />
                    </div>
                </x-form-field>
            </div>
        </div>
        </div>
        </div>
        <div class="mt-6 flex items-center justify-end gap-x-6">
            <a href="/" class="text-sm font-semibold leading-6 text-gray-900">Cancel</a>
            <x-form-button>LogIn</x-form-button>
        </div>
    </form>
</x-layout>