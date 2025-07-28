<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Semantic Category Search</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">AI Semantic Category Search</h1>
                <p class="text-lg text-gray-600">Search for services using natural language</p>
            </div>

            <!-- Search Form -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <form id="searchForm" class="flex gap-4">
                    <div class="flex-1">
                        <input type="text" id="searchQuery" name="query" value="{{ $query ?? '' }}"
                            placeholder="Enter your search query (e.g., 'furniture assembly', 'flooring installation')"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            autocomplete="off">
                    </div>
                    <button type="submit" id="searchBtn"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <span id="searchBtnText">Search</span>
                        <span id="searchBtnLoading" class="hidden">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            Searching...
                        </span>
                    </button>
                </form>
            </div>

            <!-- Results Container -->
            <div id="searchResults" class="space-y-4">
                @if (isset($results) && count($results) > 0)
                    @foreach ($results as $result)
                        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $result['service']->name }}</h3>
                                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                    {{ number_format($result['similarity'] * 100, 1) }}% match
                                </span>
                            </div>

                            <!-- Hierarchical Path -->
                            <div class="text-sm text-gray-500 mb-3">
                                {{ $result['service']->getMainCategoryName() }} →
                                {{ $result['service']->getSubCategoryName() }}
                            </div>

                            @if ($result['service']->keywords)
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-700 mb-2">Keywords:</h4>
                                    <p class="text-gray-600">{{ $result['service']->keywords }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @elseif(isset($query) && !empty($query))
                    <div class="bg-white rounded-lg shadow-md p-6 text-center">
                        <p class="text-gray-500 text-lg">No results found for "{{ $query }}"</p>
                        <p class="text-gray-400 mt-2">Try different keywords or a broader search term</p>
                    </div>
                @endif
            </div>

            <!-- Loading State -->
            <div id="loadingState" class="hidden">
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-600">Searching...</p>
                </div>
            </div>

            <!-- Error State -->
            <div id="errorState" class="hidden">
                <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                    <p class="text-red-600 font-medium">An error occurred while searching</p>
                    <p class="text-red-500 mt-2">Please try again</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let searchTimeout;

            // Handle form submission
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                performSearch();
            });

            // Handle real-time search (with debouncing)
            $('#searchQuery').on('input', function() {
                clearTimeout(searchTimeout);
                const query = $(this).val().trim();

                if (query.length >= 2) {
                    searchTimeout = setTimeout(function() {
                        performSearch();
                    }, 500); // 500ms delay
                } else if (query.length === 0) {
                    clearResults();
                }
            });

            function performSearch() {
                const query = $('#searchQuery').val().trim();

                if (!query) {
                    clearResults();
                    return;
                }

                // Show loading state
                showLoading();

                // Make AJAX request
                $.ajax({
                    url: '{{ route('search.search') }}',
                    method: 'POST',
                    data: {
                        query: query,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        hideLoading();
                        displayResults(response);
                    },
                    error: function(xhr, status, error) {
                        hideLoading();
                        showError();
                        console.error('Search error:', error);
                    }
                });
            }

            function showLoading() {
                $('#searchBtnText').addClass('hidden');
                $('#searchBtnLoading').removeClass('hidden');
                $('#searchBtn').prop('disabled', true);
                $('#loadingState').removeClass('hidden');
                $('#searchResults').addClass('hidden');
                $('#errorState').addClass('hidden');
            }

            function hideLoading() {
                $('#searchBtnText').removeClass('hidden');
                $('#searchBtnLoading').addClass('hidden');
                $('#searchBtn').prop('disabled', false);
                $('#loadingState').addClass('hidden');
            }

            function displayResults(data) {
                const resultsContainer = $('#searchResults');
                resultsContainer.empty();

                if (data.results && data.results.length > 0) {
                    data.results.forEach(function(result) {
                        const resultHtml = `
                            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-xl font-semibold text-gray-900">${result.service.name}</h3>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                                        ${(result.similarity * 100).toFixed(1)}% match
                                    </span>
                                </div>

                                <div class="text-sm text-gray-500 mb-3">
                                    ${result.service.main_category_name} → ${result.service.sub_category_name}
                                </div>

                                ${result.service.keywords ? `
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <h4 class="font-medium text-gray-700 mb-2">Keywords:</h4>
                                            <p class="text-gray-600">${result.service.keywords}</p>
                                        </div>
                                    ` : ''}
                            </div>
                        `;
                        resultsContainer.append(resultHtml);
                    });
                } else {
                    const noResultsHtml = `
                        <div class="bg-white rounded-lg shadow-md p-6 text-center">
                            <p class="text-gray-500 text-lg">No results found for "${data.query}"</p>
                            <p class="text-gray-400 mt-2">Try different keywords or a broader search term</p>
                        </div>
                    `;
                    resultsContainer.html(noResultsHtml);
                }

                resultsContainer.removeClass('hidden');
            }

            function clearResults() {
                $('#searchResults').empty().addClass('hidden');
                $('#loadingState').addClass('hidden');
                $('#errorState').addClass('hidden');
            }

            function showError() {
                $('#errorState').removeClass('hidden');
                $('#searchResults').addClass('hidden');
            }
        });
    </script>
</body>

</html>
