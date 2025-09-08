<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int $mal_id
 * @property array<array-key, mixed>|null $images
 * @property string $name
 * @property string|null $name_kanji
 * @property array<array-key, mixed>|null $nicknames
 * @property string|null $about
 * @property string $slug
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character whereAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character whereMalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character whereNameKanji($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character whereNicknames($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Character whereSlug($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCharacter {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $mal_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres ofType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres whereMalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Genres withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperGenres {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $mal_id
 * @property string $slug
 * @property string|null $type
 * @property string|null $source
 * @property int|null $episodes
 * @property string|null $status
 * @property int $airing
 * @property string|null $aired_from
 * @property string|null $aired_to
 * @property string|null $duration
 * @property string|null $rating
 * @property string|null $synopsis
 * @property string|null $background
 * @property string|null $season
 * @property string|null $broadcast
 * @property array<array-key, mixed>|null $external
 * @property int $approved
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read mixed $demographics
 * @property-read mixed $display_title
 * @property-read mixed $explicit_genres
 * @property-read mixed $genres
 * @property-read mixed $themes
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostImage> $images
 * @property-read int|null $images_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostMorphable> $morphables
 * @property-read int|null $morphables_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostCharacter> $postCharacters
 * @property-read int|null $post_characters_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostProducer> $postProducers
 * @property-read int|null $post_producers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostTitle> $titles
 * @property-read int|null $titles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PostVideo> $videos
 * @property-read int|null $videos_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereAiredFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereAiredTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereAiring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereBackground($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereBroadcast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereEpisodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereExternal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereMalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereSeason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereSynopsis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Post withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPost {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $post_id
 * @property int $character_id
 * @property string $role
 * @property-read \App\Models\Character $character
 * @property-read \App\Models\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCharacter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCharacter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCharacter query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCharacter whereCharacterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCharacter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCharacter wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostCharacter whereRole($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPostCharacter {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $post_id
 * @property string|null $image_url
 * @property string|null $alt_text
 * @property string $image_type
 * @property string|null $language
 * @property bool|null $is_primary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPostImage {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $post_id
 * @property int $morphable_id
 * @property string $morphable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $morphable
 * @property-read \App\Models\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable whereMorphableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable whereMorphableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostMorphable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPostMorphable {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $post_id
 * @property int $producer_id
 * @property string $type
 * @property-read \App\Models\Post|null $post
 * @property-read \App\Models\Producer|null $producer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostProducer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostProducer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostProducer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostProducer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostProducer wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostProducer whereProducerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostProducer whereType($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPostProducer {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $post_id
 * @property string $title
 * @property string $type
 * @property string|null $language
 * @property int|null $is_primary
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostTitle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPostTitle {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $post_id
 * @property string|null $title
 * @property string|null $url
 * @property array<array-key, mixed>|null $meta
 * @property string $video_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $embed_url
 * @property-read mixed $thumbnail_url
 * @property-read mixed $youtube_id
 * @property-read \App\Models\Post $post
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo whereMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo wherePostId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PostVideo whereVideoType($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPostVideo {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $mal_id
 * @property string $slug
 * @property array<array-key, mixed> $titles
 * @property array<array-key, mixed> $images
 * @property string|null $established
 * @property string|null $about
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereEstablished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereMalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereTitles($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Producer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperProducer {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

