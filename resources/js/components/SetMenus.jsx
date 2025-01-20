import React, { useState, useEffect } from 'react';
import axios from 'axios';

const SetMenus = () => {
    const [state, setState] = useState({
        setMenus: [],
        filters: {
            cuisines: [],
            sort_options: [],
            active_filters: {
                cuisine_slug: '',
                sort_by: '',
                guests: 1
            }
        },
        loading: true,
        error: null,
        meta: {
            current_page: 1,
            last_page: 1,
            per_page: 9,
            total: 0
        }
    });

    useEffect(() => {
        loadData();
    }, [
        state.filters.active_filters.cuisine_slug,
        state.filters.active_filters.sort_by,
        state.filters.active_filters.guests
    ]);

    const loadData = async (page = 1) => {
        try {
            setState(prev => ({ ...prev, loading: true, error: null }));

            const response = await axios.get('/api/set-menus', {
                params: {
                    page,
                    per_page: state.meta.per_page,
                    cuisine_slug: state.filters.active_filters.cuisine_slug,
                    sort_by: state.filters.active_filters.sort_by,
                    guests: state.filters.active_filters.guests
                }
            });

            setState(prev => ({
                ...prev,
                setMenus: page === 1 
                    ? response.data.setMenus.data 
                    : [...prev.setMenus, ...response.data.setMenus.data],
                filters: {
                    ...response.data.filters,
                    active_filters: {
                        ...prev.filters.active_filters,
                        ...response.data.filters.active_filters
                    }
                },
                meta: response.data.meta,
                loading: false
            }));

        } catch (error) {
            setState(prev => ({
                ...prev,
                error: 'Failed to load menus',
                loading: false
            }));
        }
    };

    const handleFilterChange = (filterType, value) => {
        setState(prev => ({
            ...prev,
            filters: {
                ...prev.filters,
                active_filters: {
                    ...prev.filters.active_filters,
                    [filterType]: value
                }
            },
            meta: {
                ...prev.meta,
                current_page: 1
            }
        }));
    };

    const loadMore = () => {
        if (!state.loading && state.meta.current_page < state.meta.last_page) {
            loadData(state.meta.current_page + 1);
        }
    };

    const calculateTotalPrice = (menu) => {
        const total = menu.price_per_person * state.filters.active_filters.guests;
        return Math.max(total, menu.min_spend);
    };

    return (
        <div className="min-h-screen bg-gray-50 py-8">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Filters Section */}
                <div className="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {/* Cuisine Filter */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Cuisine
                            </label>
                            <select
                                value={state.filters.active_filters.cuisine_slug}
                                onChange={(e) => handleFilterChange('cuisine_slug', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">All Cuisines</option>
                                {state.filters.cuisines.map(cuisine => (
                                    <option key={cuisine.slug} value={cuisine.slug}>
                                        {cuisine.name} ({cuisine.set_menus_count})
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Sort Filter */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Sort By
                            </label>
                            <select
                                value={state.filters.active_filters.sort_by}
                                onChange={(e) => handleFilterChange('sort_by', e.target.value)}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            >
                                <option value="">Default</option>
                                {state.filters.sort_options.map(option => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        </div>

                        {/* Guests Input */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Number of Guests
                            </label>
                            <input
                                type="number"
                                min="1"
                                value={state.filters.active_filters.guests}
                                onChange={(e) => handleFilterChange('guests', Math.max(1, parseInt(e.target.value) || 1))}
                                className="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            />
                        </div>
                    </div>
                </div>

                {/* Error Message */}
                {state.error && (
                    <div className="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                        <div className="flex">
                            <div className="flex-shrink-0">
                                <svg className="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <p className="text-sm text-red-700">{state.error}</p>
                            </div>
                        </div>
                    </div>
                )}

                {/* Menu Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {state.setMenus.map(menu => (
                        <div key={menu.id} className="bg-white rounded-lg shadow-sm overflow-hidden">
                            {menu.image && (
                                <div className="aspect-w-16 aspect-h-9">
                                    <img
                                        src={menu.image}
                                        alt={menu.name}
                                        className="w-full h-48 object-cover"
                                    />
                                </div>
                            )}
                            <div className="p-6">
                                <h3 className="text-lg font-semibold text-gray-900">{menu.name}</h3>
                                <p className="mt-2 text-sm text-gray-500">{menu.description}</p>
                                <div className="mt-4">
                                    <p className="text-sm text-gray-600">Price per person: £{menu.price_per_person}</p>
                                    <p className="text-sm text-gray-600">Minimum spend: £{menu.min_spend}</p>
                                    <p className="text-lg font-bold text-green-600 mt-2">
                                        Total for {state.filters.active_filters.guests} guests: £{calculateTotalPrice(menu)}
                                    </p>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Load More Button */}
                {state.meta.current_page < state.meta.last_page && (
                    <div className="mt-8 text-center">
                        <button
                            onClick={loadMore}
                            disabled={state.loading}
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                        >
                            {state.loading ? 'Loading...' : 'Load More'}
                        </button>
                    </div>
                )}
            </div>
        </div>
    );
};

export default SetMenus;