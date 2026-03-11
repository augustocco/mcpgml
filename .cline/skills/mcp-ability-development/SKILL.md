---
name: mcp-ability-development
description: Create and register MCP abilities using WordPress Abilities API. Use when developing new capabilities that expose WordPress functionality as MCP tools.
---

# MCP Ability Development

## Overview

This skill provides guidance for creating MCP (Model Context Protocol) abilities that expose WordPress functionality as tools for AI agents. The project uses the WordPress Abilities API to register these capabilities.

## Architecture

```
WordPress → Abilities API → MCP Adapter → AI Agent
```

1. **WordPress**: Core functionality (LearnDash, Posts, Users, etc.)
2. **Abilities API**: Registration system for abilities
3. **MCP Adapter**: Exposes abilities as MCP tools
4. **AI Agent**: Uses tools via MCP protocol

## Ability Structure

Every MCP ability has:

1. **Metadata**: Name, label, description, category
2. **Input Schema**: JSON Schema for request parameters
3. **Output Schema**: JSON Schema for response structure
4. **Execute Callback**: Function that performs the action
5. **Permission Callback**: Function that checks user permissions

## Creating a New Ability

### Basic Template

```php
<?php
/**
 * Plugin Name: My MCP Ability
 */

add_action( 'wp_abilities_api_init', function() {
    if ( ! function_exists( 'wp_register_ability' ) ) {
        return;
    }
    
    wp_register_ability( 'plugin-name/ability-name', [
        'label' => 'Human Readable Label',
        'description' => 'Description of what this ability does',
        'category' => 'learndash', // or 'support', 'wordpress'
        
        // Input schema - what parameters the ability accepts
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'param_name' => [
                    'type' => 'string',
                    'description' => 'Parameter description',
                    'default' => 'default_value'
                ],
                'numeric_param' => [
                    'type' => 'integer',
                    'description' => 'Numeric parameter description'
                ]
            ],
            'required' => ['param_name']
        ],
        
        // Output schema - what the ability returns
        'output_schema' => [
            'type' => 'object',
            'properties' => [
                'result_field' => [
                    'type' => 'string'
                ],
                'success' => [
                    'type' => 'boolean'
                ]
            ]
        ],
        
        // Function that executes the ability
        'execute_callback' => function( $input ) {
            // 1. Validate and sanitize inputs
            $param = sanitize_text_field( $input['param_name'] );
            
            // 2. Perform the action
            $result = do_something( $param );
            
            // 3. Return structured result
            return [
                'success' => true,
                'result_field' => $result
            ];
        },
        
        // Permission check
        'permission_callback' => function() {
            return current_user_can( 'manage_options' );
        }
    ] );
}, 10 );
```

## Input Schema Patterns

### Common Types

```php
// String parameter
'username' => [
    'type' => 'string',
    'description' => 'User username'
]

// Integer parameter
'user_id' => [
    'type' => 'integer',
    'description' => 'WordPress user ID'
]

// Boolean parameter
'include_deleted' => [
    'type' => 'boolean',
    'description' => 'Whether to include deleted items'
]

// Array parameter
'post_ids' => [
    'type' => 'array',
    'description' => 'List of post IDs',
    'items' => [
        'type' => 'integer'
    ]
]

// Enum parameter
'status' => [
    'type' => 'string',
    'description' => 'Post status',
    'enum' => ['publish', 'draft', 'pending']
]
```

### LearnDash-Specific Patterns

```php
// Course ID
'course_id' => [
    'type' => 'integer',
    'description' => 'LearnDash course ID'
]

// User ID with validation
'user_id' => [
    'type' => 'integer',
    'description' => 'WordPress user ID (0 for current user)'
]

// Lesson/Topic ID
'step_id' => [
    'type' => 'integer',
    'description' => 'Lesson or topic ID'
]
```

## Output Schema Patterns

```php
// Success response
'success' => [
    'type' => 'boolean'
]

// Data array
'data' => [
    'type' => 'array',
    'description' => 'Array of results',
    'items' => [
        'type' => 'object'
    ]
]

// Pagination
'pagination' => [
    'type' => 'object',
    'properties' => [
        'total' => ['type' => 'integer'],
        'page' => ['type' => 'integer'],
        'per_page' => ['type' => 'integer']
    ]
]

// Error response
'error' => [
    'type' => 'object',
    'properties' => [
        'code' => ['type' => 'string'],
        'message' => ['type' => 'string']
    ]
]
```

## Best Practices

### 1. Always Sanitize Inputs

```php
// ❌ Bad
$user_id = $input['user_id'];

// ✅ Good
$user_id = absint( $input['user_id'] );
$username = sanitize_user( $input['username'] );
$email = sanitize_email( $input['email'] );
$text = sanitize_text_field( $input['text'] );
```

### 2. Handle Errors Gracefully

```php
// Return WP_Error for issues
if ( empty( $user_id ) ) {
    return new WP_Error(
        'invalid_user',
        'User ID is required',
        ['status' => 400]
    );
}

// Or return structured error in response
if ( ! $user ) {
    return [
        'success' => false,
        'error' => [
            'code' => 'user_not_found',
            'message' => 'User not found'
        ]
    ];
}
```

### 3. Use WordPress Functions

```php
// Get current user
$user_id = get_current_user_id();

// Check capability
if ( ! current_user_can( 'edit_posts' ) ) {
    return new WP_Error( 'forbidden', 'Insufficient permissions' );
}

// Get post
$post = get_post( $post_id );

// Query posts
$posts = get_posts([
    'post_type' => 'sfwd-courses',
    'posts_per_page' => -1
]);
```

### 4. Performance Considerations

```php
// Use wp_cache_get/set for expensive operations
$cache_key = "course_progress_{$user_id}_{$course_id}";
$result = wp_cache_get( $cache_key );

if ( false === $result ) {
    $result = calculate_expensive_data();
    wp_cache_set( $cache_key, $result, '', 3600 );
}

return $result;
```

## Common Ability Categories

### LearnDash Abilities

```php
// Progress tracking
'learndash/get-user-progress'
'learndash/get-enrolled-courses'
'learndash/get-course-steps'

// Content management
'learndash/get-course-content'
'learndash/get-lesson-data'
'learndash/get-quiz-results'

// User actions
'learndash/mark-complete'
'learndash/reset-progress'
```

### WordPress Abilities

```php
// User management
'wordpress/get-user'
'wordpress/get-user-meta'
'wordpress/update-user-meta'

// Post management
'wordpress/get-post'
'wordpress/create-post'
'wordpress/update-post'

// Site info
'wordpress/get-site-info'
'wordpress/get-options'
```

### Support Abilities

```php
// Diagnostics
'support/check-plugin-status'
'support/get-system-info'
'support/verify-configuration'
```

## Testing Abilities

### Create Test File

```php
// test_my_ability.php
<?php
// Test script for my ability

require_once( __DIR__ . '/wp-load.php' );

// Test with current user
$input = [
    'course_id' => 123
];

// Call the ability
$result = wp_abilities_api_execute( 'plugin-name/ability-name', $input );

echo '<pre>';
print_r( $result );
echo '</pre>';
```

### Test via MCP Adapter

```bash
# Start MCP server
node mcp-stdio-wrapper.bat

# Test ability via MCP client
# (implementation depends on MCP client being used)
```

## Debugging

### Enable Logging

```php
// Log ability execution
error_log( sprintf(
    'Ability %s called with input: %s',
    'plugin-name/ability-name',
    print_r( $input, true )
) );
```

### Check Registration

```php
// List all registered abilities
$abilities = wp_abilities_api_get_all();
error_log( print_r( $abilities, true ) );
```

## Resources

- WordPress Abilities API: `wordpress/wp-content/plugins/abilities-api/docs/`
- MCP Adapter: `wordpress/wp-content/plugins/mcp-adapter/`
- Example Abilities: `wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/`
- MCP Protocol: https://modelcontextprotocol.io/
- JSON Schema: https://json-schema.org/