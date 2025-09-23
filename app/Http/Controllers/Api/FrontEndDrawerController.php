<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FrontEndDrawerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $links = [
            'links' => [
                'links1' => [
                    [
                        'icon' => 'face',
                        'text' => 'Genre',
                        'name' => 'genre'
                    ],
                    [
                        'icon' => 'whatshot',
                        'text' => 'Trending',
                        'name' => 'trending'
                    ],
                    [
                        'icon' => 'subscriptions',
                        'text' => 'Subscriptions',
                        'name' => 'subscriptions'
                    ]
                ],
                'links2' => [
                    [
                        'icon' => 'folder',
                        'text' => 'Library'
                    ],
                    [
                        'icon' => 'restore',
                        'text' => 'History'
                    ],
                    [
                        'icon' => 'watch_later',
                        'text' => 'Watch later'
                    ],
                    [
                        'icon' => 'thumb_up_alt',
                        'text' => 'Liked videos'
                    ]
                ],
                'links3' => [
                    [
                        'icon' => 'fabYoutube',
                        'text' => 'YouTube Premium'
                    ],
                    [
                        'icon' => 'local_movies',
                        'text' => 'Movies & Shows'
                    ],
                    [
                        'icon' => 'videogame_asset',
                        'text' => 'Gaming'
                    ],
                    [
                        'icon' => 'live_tv',
                        'text' => 'Live'
                    ]
                ],
                'links4' => [
                    [
                        'icon' => 'settings',
                        'text' => 'Settings'
                    ],
                    [
                        'icon' => 'flag',
                        'text' => 'Report history'
                    ],
                    [
                        'icon' => 'help',
                        'text' => 'Help'
                    ],
                    [
                        'icon' => 'feedback',
                        'text' => 'Send feedback'
                    ]
                ]
            ],
            'buttons' => [
                'buttons1' => [
                    [
                        'text' => 'About'
                    ],
                    [
                        'text' => 'Press'
                    ],
                    [
                        'text' => 'Copyright'
                    ],
                    [
                        'text' => 'Contact us'
                    ],
                    [
                        'text' => 'Creators'
                    ],
                    [
                        'text' => 'Advertise'
                    ],
                    [
                        'text' => 'Developers'
                    ]
                ],
                'buttons2' => [
                    [
                        'text' => 'Terms'
                    ],
                    [
                        'text' => 'Privacy'
                    ],
                    [
                        'text' => 'Policy & Safety'
                    ],
                    [
                        'text' => 'Test new features'
                    ]
                ]
            ]
        ];

        return response()->json($links);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
