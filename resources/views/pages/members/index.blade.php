<x-content title="Manage Members">
    <div class="-m-6 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="py-2 align-middle inline-block min-w-full sm:px-2 lg:px-4">
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
                            {{ __('Status') }}
                        </th>
                        <th scope="col" class="relative px-6 py-3">
                            <span class="sr-only">{{ __('Details') }}</span>
                        </th>
                        {{--                            <th scope="col" class="relative px-6 py-3">--}}
                        {{--                                <span class="sr-only">Edit</span>--}}
                        {{--                            </th>--}}
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
                                {{ $member->email }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{ \App\Instruments::INSTRUMENT_GROUPS[$member->personalData->instrument]['name'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColor = 'bg-orange-100 text-orange-800';
                                    $status = __('New');

                                    if($member->status === \App\Models\User::STATUS_UNLOCKED) {
                                        $statusColor = 'bg-green-100 text-green-800';
                                        $status = __('Active');
                                    }
                                    if($member->status === \App\Models\User::STATUS_LOCKED) {
                                        $statusColor = 'bg-red-100 text-red-800';
                                        $status = __('Locked');
                                    }
                                @endphp
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('members.show', ['member' => $member]) }}"
                                   class="text-indigo-600 hover:text-indigo-900">{{ __('Details') }}</a>
                            </td>
                            {{--                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">--}}
                            {{--                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>--}}
                            {{--                                </td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{ $members->links() }}
</x-content>
