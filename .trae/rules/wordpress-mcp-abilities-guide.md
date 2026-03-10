# WordPress MCP Abilities Guide

Schema Rules: Input/Output DEBE incluir `type`,`properties`. NO DEBE incluir `required` o `additionalProperties=>false`.

Registration: `name`,`label`,`category`,`description`,`input_schema`,`output_schema`,`execute_callback`,`permission_callback`,`meta`.

Meta: `show_in_rest=>true`,`mcp=>array('public'=>true,'type'=>'tool')`,`annotations=>array('readonly'=>true|false)`.

Avoid: No usar `required`, no usar `additionalProperties=>false`, usar `__()` para traducción.

Hooks: Categorías en `wp_abilities_api_categories_init`, habilidades en `wp_abilities_api_init`.
