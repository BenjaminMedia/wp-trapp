# WP TRAPP Plugin
Send content to the TRAPP translation service.

## Installation

As composer is very optional in WordPress community there are two ways to install this plugin.

### Composer

**If the project is loading the main composer autoload file.**

Install plugin:

`composer create-project benjaminmedia/wp-trapp 1.*`

Install plugin as working dev/master version:

`composer create-project benjaminmedia/wp-trapp --stability dev --prefer-dist`

Install plugin as a working dev/master version with all vcs files:

`composer create-project benjaminmedia/wp-trapp --stability dev --prefer-source --keep-vcs`

### WP Plugin

**If the plugin should not rely on anything other than its own composer functionality.**

Install plugin:

`composer require benjaminmedia/wp-trapp 1.*`

Install plugin as working dev/master version:

`composer require benjaminmedia/wp-trapp dev-master --prefer-dist`

Install plugin as a working dev/master version with all vcs files:

`composer require benjaminmedia/wp-trapp dev-master --prefer-source`

### Setup

TRAPP requires some information like username and password. This is how they can be defined.

`Username`

Define either `WA_TRAPP_USERNAME` or from the `bp_trapp_service_username` filter.

```
add_filter('bp_trapp_service_username', function() {
{
    return 'myuser';
});
```

`Secret`

Define either `WA_TRAPP_SECRET` or from the `bp_trapp_service_secret` filter.

```
add_filter('bp_trapp_service_secret', function() {
{
    return 'mysecret';
});
```

`Development`

Default is false.
If `APP_ENV` is found it will return true if `APP_ENV` is production.
Change how development is defined in the `bp_trapp_service_development` filter.

```
add_filter('bp_trapp_service_development', function($isDevelopment) {
{
    if (defined('WP_DEBUG') && WP_DEBUG) {
        return true;
    }

    return $isDevelopment;
});
```

`APP Code`

Define either `WA_APP_CODE` or from the `bp_trapp_save_app_code` filter.

```
add_filter('bp_trapp_save_app_code', function() {
{
    return 'my_app_code';
});
```

`Brand Code`

Define either `WA_BRAND_CODE` or from the `bp_trapp_save_brand_code` filter.

```
add_filter('bp_trapp_save_brand_code', function() {
{
    return 'my_brand_code';
});
```

### Extend (Hooks)

`bp_trapp_post_types`

Custom post types are whitelisted in this filter. If the 'mycpt' custom post type is suppose to send translations to TRAPP this is the filter for it.

- `$post_types` Array values of post types

```
/**
 * Adds 'mycpt' to the accepted TRAPP post types.
 */
add_filter('bp_trapp_post_types', function($post_types) {
    $post_types[] = 'mycpt';

    return $post_types;
});
```

`bp_trapp_save_{$post_type}_taxonomies`

Enables which taxonomies that should be copied to the new translation from the master. This will copy the translated versions of the terms from the master.

The `{$post_type}` is a dynamic filter and is specific to the `{$post_type}`

- `$taxonomies` Array values of taxonomies

```
/**
 * Clone taxonomy 'myct' when a 'mycpt' post type is translated.
 */
add_filter('bp_trapp_save_mycpt_taxonomies', function($taxonomies) {
    $taxonomies[] = 'myct';

    return $taxonomies;
});
```

`bp_trapp_locked_field`

- `$return` The return value.
- `$field` Array data of the current field

Passes input names to js to disable these fields in the edit post view. Use this filter if the name of the metadata does not match the input name output in HTML.

```
/**
 * The field '_mycustomfield' is saved from the input[name="mycustomfield_textinput"].
 */
add_filter('bp_trapp_locked_field', function($return, $field) {
    if ($field['type'] == 'post_meta' && $field['args']['key'] == '_mycustomfield') {
        return 'mycustomfield_textinput';
    }

    return $return;
}, 12, 2);
```

`bp_trapp_get_{$post_type}_fields`

Use this filter to modify the fields sent and updated from TRAPP. This can be used to unset fields, modify labels or add new fields.

The `{$post_type}` is a dynamic filter and is specific to the `{$post_type}`

- `$fields` Array of field groups containing fields

```
/**
 * Change the "Post Thumbnail" to "Main Image"
 */
add_filter('bp_trapp_get_mycpt_fields', function($fields) {
    if (array_key_exists('post_thumbnail', $fields)) {
        $fields['post_thumbnail']['title'] = 'Main Image';

        foreach ($fields['post_thumbnail']['fields'] as $key => $field) {
            $fields['post_thumbnail']['fields'][$key]['label'] = str_replace('Post Thumbnail', 'Main Image', $field['label']);
        }
    }
});

/**
 * Extend TRAPP with my field group.
 */
add_filter('bp_trapp_get_mycpt_fields', function($fields) {
    $fields['my_field_group'] = [
        'title' => 'My field group',
        'fields' => [
            'title' => [
                'label' => 'My field group title',
                'args' => [
                    'key' => 'mfg_title'
                ],
                'type' => 'post_meta',
            ],
            'text' => [
                'label' => 'My field group text',
                'args' => [
                    'key' => 'mfg_teaser_text'
                ],
                'type' => 'post_meta',
            ],
        ],
    ];
});

/**
 * Extend TRAPP with my image field group.
 */
add_filter('bp_trapp_get_mycpt_image_fields', function($fields) {
    $fields['my_image_field_group'] = [
        'title' => 'My Image',
        'fields' => [
            'display_image' => [
                'label' => 'My image Url',
                'args' => [
                    'image_key' => 'mifg_image',
                ],
                'type' => 'image_display',
                'display_format' => 'image',
            ],
            'title' => [
                'label' => 'My image Title',
                'args' => [
                    'image_key' => 'mifg_image',
                    'key' => 'post_title'
                ],
                'type' => 'image_wp_post',
            ],
            'alt' => [
                'label' => 'My image Alt',
                'args' => [
                    'image_key' => 'mifg_image',
                    'key' => '_wp_attachment_image_alt'
                ],
                'type' => 'image_post_meta',
            ],
        ],
    ];
});
```

`bp_trapp_save_images`

Add images that should get saved whenever a post is getting translated.

- `$images` Array of images containing image data
- `$postId` Post ID of the post

```
add_filter('bp_trapp_save_images', function($images, $postId) {
    $secondaryImage = get_post_meta($postId, 'secondary_image', true);

    if ($secondaryImage) {
        $images['secondary_image'] = [
            'id' => $secondaryImage,
            'post' => get_post($secondaryImage),
            'type' => 'meta',
            'key' => 'secondary_image',
        ];
    }

    return $images;
}, 10, 2);
```

`bp_trapp_after_save_post_image`

Do something after a translated image has been saved.

- `$translationImageId` ID of the translated ID
- `$image` Array of translated from image data

Example:
```
/**
 * Add metadata to new translated image from the master image.
 */
add_action('bp_trapp_after_save_post_image', function($translationImageId, $image) {
    add_post_meta($translationImageId, 'custom_seo_field', get_post_meta($image['id'], 'custom_seo_field', true));
}, 10, 2);

```
