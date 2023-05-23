<div>
    <div class="flex items-center justify-between">
        <div>
            <span class="mr-2">
                {{ __('Sort By') }}:
            </span>
            <x-select wire:model="sortBy" wire:change="sort">
                <option value="id">{{ __('Created At') }}</option>
                <option value="name">{{ __('Name') }}</option>
                <option value="street">{{ __('Street and Housenumber') }}</option>
                <option value="zip">{{ __('Zip Code') }}</option>
                <option value="city">{{ __('City') }}</option>
                <option value="instrument">{{ __('Instrument') }}</option>
            </x-select>
        </div>

        <livewire:export-users/>
    </div>

    <div class="border-t border-gray-200 mt-4 mb-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="pb-2 align-middle inline-block min-w-full sm:px-2 lg:px-4">
            <div class="overflow-hidden border-b border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('ID') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Name') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Email') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Instrument') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Address') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Mobile Phone') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Status') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Admin') }}
                        </th>
                        <th scope="col"
                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ __('Can view all sheets') }}
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">{{ __('Edit') }}</span>
                        </th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($members as $member)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $member->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $member->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ join(', ', [$member->email, ...$member->additionalEmails->map(fn($e) => $e->email)]) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $member->instrumentGroups->implode('title', ', ') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $member->personalData->street }},
                                {{ $member->personalData->zip }}
                                {{ $member->personalData->city }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ $member->personalData->mobile_phone }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-status :status="$member->status"/>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->hasRole(\App\Rights::R_ADMIN))
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 block mx-auto text-green-500"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($member->can(\App\Rights::P_VIEW_ALL_INSTRUMENTS))
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 block mx-auto text-green-500"
                                         fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('members.edit', ['member' => $member]) }}"
                                   class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div>
        {{ $members->links() }}
    </div>
</div>
