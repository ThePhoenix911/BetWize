<div class="p-6 bg-white shadow rounded-lg">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">{{ $league->name }}</h2>

    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
        <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Club Name</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API ID</th>
        </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        @foreach($league->clubs as $club)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $club->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $club->short_code }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $club->api_id }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
