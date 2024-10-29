<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('products.update', @$product) }}">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <x-label for="name" :value="__('Name')" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="$product->name" required autofocus />
            </div>

            <div>
                <x-label for="price" :value="__('Price')" />
                <x-input id="price" class="block mt-1 w-full" type="text" name="price" :value="$product->price" required autofocus />
            </div>


            <div class="flex items-center justify-end mt-4">
                <x-button class="ml-4">{{ __('Update') }}</x-button>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>
