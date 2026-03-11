---
name: learndash-integration
description: Integrate and work with LearnDash LMS functionality. Use when creating courses, lessons, quizzes, managing student progress, or implementing LearnDash-specific features.
---

# LearnDash Integration

## Overview

This skill provides guidance for working with LearnDash LMS in the MCPGML project. LearnDash is the primary LMS used for course management.

## Key Concepts

### Course Structure
- **Courses**: Main container for learning content
- **Lessons**: Individual learning modules
- **Topics**: Sub-sections within lessons
- **Quizzes**: Assessments for learners
- **Groups**: Collections of learners
- **Certificates**: Completion awards

### Post Types
- `sfwd-courses`: Courses
- `sfwd-lessons`: Lessons
- `sfwd-topic`: Topics
- `sfwd-quiz`: Quizzes
- `sfwd-certificates`: Certificates

## Common Tasks

### Get User Progress

```php
$user_id = get_current_user_id();
$course_id = 123;

// Get course progress
$progress = learndash_user_get_course_progress( $user_id, $course_id );

// Get percentage completed
$percentage = learndash_course_get_completed_percentage( $user_id, $course_id );
```

### Get Enrolled Courses

```php
$user_id = get_current_user_id();

// Get all enrolled courses
$courses = ld_get_mycourses( $user_id );

// Get course IDs
$course_ids = learndash_user_enrolled_courses( $user_id );
```

### Check if User Has Access

```php
$user_id = get_current_user_id();
$course_id = 123;

// Check if user is enrolled
if ( sfwd_lms_has_access( $course_id, $user_id ) ) {
    // User has access
}
```

### Get Course Steps

```php
$course_id = 123;

// Get all lessons in course
$lessons = learndash_course_get_lessons( $course_id );

// Get all quizzes in course
$quizzes = learndash_course_get_quizzes( $course_id );

// Get course steps (lessons, topics, quizzes)
$steps = learndash_course_get_steps( $course_id );
```

### Mark Lesson Complete

```php
$user_id = get_current_user_id();
$lesson_id = 456;
$course_id = 123;

// Mark lesson as complete
learndash_process_mark_incomplete( $user_id, $lesson_id, $course_id );
```

## MCP Ability Integration

When creating MCP abilities for LearnDash:

### Ability Registration Pattern

```php
add_action( 'wp_abilities_api_init', function() {
    wp_register_ability( 'learndash/get-user-progress', [
        'label' => 'Get User Progress',
        'description' => 'Get progress for a user in a specific course',
        'category' => 'learndash',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'user_id' => [
                    'type' => 'integer',
                    'description' => 'WordPress user ID'
                ],
                'course_id' => [
                    'type' => 'integer',
                    'description' => 'LearnDash course ID'
                ]
            ],
            'required' => ['user_id', 'course_id']
        ],
        'output_schema' => [
            'type' => 'object',
            'properties' => [
                'completed_steps' => ['type' => 'integer'],
                'total_steps' => ['type' => 'integer'],
                'percentage' => ['type' => 'number']
            ]
        ],
        'execute_callback' => function( $input ) {
            $user_id = absint( $input['user_id'] );
            $course_id = absint( $input['course_id'] );
            
            $progress = learndash_user_get_course_progress( $user_id, $course_id );
            $percentage = learndash_course_get_completed_percentage( $user_id, $course_id );
            
            return [
                'completed_steps' => $progress['completed'],
                'total_steps' => $progress['total'],
                'percentage' => $percentage
            ];
        },
        'permission_callback' => function() {
            return current_user_can( 'edit_posts' );
        }
    ] );
} );
```

### Common Ability Patterns

- `learndash/get-user-progress`: Get student progress
- `learndash/get-enrolled-courses`: List user's courses
- `learndash/get-course-content`: Get course structure
- `learndash/mark-complete`: Mark lesson/topic complete
- `learndash/get-quiz-results`: Get quiz scores
- `learndash/certificate-data`: Generate certificate

## Security Considerations

- Always validate user IDs with `absint()`
- Check permissions before accessing course data
- Use `current_user_can()` to verify access rights
- Never expose sensitive student data without proper authorization

## Debugging LearnDash Issues

1. Check if LearnDash is active: `defined( 'LEARNDASH_VERSION' )`
2. Verify user enrollment: `sfwd_lms_has_access( $course_id, $user_id )`
3. Check course status: `get_post_status( $course_id )`
4. Review LearnDash settings in WordPress admin

## Resources

- LearnDash Documentation: https://www.learndash.com/support/docs/
- Code Examples: See `wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/class-learndash-abilities.php`
- LearnDash Functions: See `wordpress/wp-content/plugins/sfwd-lms/includes/ld-course-legacy.php`