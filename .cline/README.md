# Cline Configuration for MCPGML

This directory contains Cline-specific configuration files for the MCPGML project.

## Structure

```
.cline/
├── skills/
│   ├── learndash-integration/
│   │   └── SKILL.md
│   └── mcp-ability-development/
│       └── SKILL.md
└── README.md (this file)

.clinerules/
├── 01-universal-standards.md
├── 02-wordpress-plugins.md
├── 03-theme-development.md
└── workflows/
    ├── create-mcp-ability.md
    ├── test-mcp-abilities.md
    ├── release-preparation.md
    ├── debug-learndash.md
    └── setup-project.md
```

## Rules (.clinerules/)

Rules are persistent instructions that apply across all conversations. They are organized by scope:

### 01-universal-standards.md
- **Scope**: Always active (no path conditions)
- **Content**: Universal coding standards, security requirements, naming conventions
- **Applies to**: All files in the project

### 02-wordpress-plugins.md
- **Scope**: Conditional - activates when working with plugin files
- **Paths**: `wordpress/wp-content/plugins/**`
- **Content**: WordPress plugin development patterns, ability registration, LearnDash integration

### 03-theme-development.md
- **Scope**: Conditional - activates when working with theme files
- **Paths**: `wordpress/wp-content/themes/**`
- **Content**: Theme development standards, CSS conventions, shortcodes

## Skills (.cline/skills/)

Skills are modular instruction sets that load on-demand for specific tasks.

### learndash-integration/
- **When to use**: Working with LearnDash LMS functionality
- **Triggers**: "courses", "lessons", "quizzes", "student progress", "LearnDash"
- **Contents**:
  - LearnDash API functions
  - Course/lesson/quiz management
  - Student progress tracking
  - MCP ability patterns for LearnDash

### mcp-ability-development/
- **When to use**: Creating new MCP abilities
- **Triggers**: "MCP ability", "register ability", "expose functionality", "MCP tool"
- **Contents**:
  - Ability registration patterns
  - Input/output schema examples
  - Best practices for ability development
  - Testing and debugging guide

## Workflows (.clinerules/workflows/)

Workflows are Markdown files that define a series of steps to automate repetitive or complex tasks. Type `/` followed by the workflow's filename to invoke it (e.g., `/create-mcp-ability.md`).

### create-mcp-ability.md
- **When to use**: Creating a new MCP ability
- **Description**: Guides the process of creating, registering, and testing a new MCP ability following project standards
- **Steps**:
  1. Gather ability information (name, description, category, parameters)
  2. Create the ability PHP file with proper template
  3. Update the main plugin file to include the new ability
  4. Create a test file for development
  5. Verify registration patterns
  6. Commit with proper format
  7. Clean up test files

### test-mcp-abilities.md
- **When to use**: Testing MCP abilities (existing or new)
- **Description**: Helps verify that MCP abilities work correctly
- **Steps**:
  1. List available abilities
  2. Select ability to test
  3. Get ability information and schema
  4. Prepare test parameters
  5. Execute the ability
  6. Analyze results
  7. Document findings
  8. Optional: stress testing, permission testing, comparisons

### release-preparation.md
- **When to use**: Preparing a new project release
- **Description**: Automates the pre-release checklist including verification, version bumping, and changelog generation
- **Steps**:
  1. Verify clean working directory
  2. Check current branch
  3. Pull latest changes
  4. Find and clean test files
  5. Update version numbers
  6. Generate changelog from commits
  7. Create git tag
  8. Push changes and tags
  9. Verify documentation
  10. Show release summary

### debug-learndash.md
- **When to use**: Diagnosing and resolving LearnDash LMS issues
- **Description**: Guides the process of identifying and fixing LearnDash problems
- **Steps**:
  1. Verify LearnDash status
  2. Check existing courses
  3. Verify registered users
  4. Gather problem details
  5. Check error logs
  6. Verify dependencies
  7. Test specific functionality
  8. Run targeted tests
  9. Analyze results
  10. Document issues
  11. Verify solutions

### setup-project.md
- **When to use**: Setting up development environment from scratch or for new developers
- **Description**: Configures the MCPGML development environment including WordPress, plugins, and MCP connection
- **Steps**:
  1. Verify system requirements
  2. Check Docker/Kubernetes (optional)
  3. Clone repository
  4. Install WordPress dependencies
  5. Configure database
  6. Install and activate plugins
  7. Activate theme
  8. Configure MCP Adapter
  9. Test MCP connection
  10. Create test user (optional)
  11. Verify Cline configuration
  12. Show setup summary and next steps

## How They Work Together

- **Rules**: Always provide context about coding standards and project conventions
- **Skills**: Load detailed guidance only when relevant to the current task
- **Workflows**: Automate multi-step processes with a single command
- **Combined effect**: Cline has universal standards, specialized knowledge, and automated workflows when needed

## Toggling

You can toggle individual rules and skills in the Cline UI:
- Rules panel shows all available rules with on/off toggles
- Skills panel shows all available skills with on/off toggles
- Conditional rules only appear when their path conditions are met
- Skills only activate when triggered by your request

## Customization

To add new rules, skills, or workflows:

1. **For Rules**: Create `.md` files in `.clinerules/`
   - Use YAML frontmatter for path conditions (optional)
   - Files without frontmatter are always active
   - Use numeric prefixes for ordering (e.g., `04-new-rule.md`)

2. **For Skills**: Create directories in `.cline/skills/`
   - Each skill must have a `SKILL.md` file
   - Directory name must match `name` in frontmatter
   - Write descriptive `description` (max 1024 chars)
   - Include clear instructions in the SKILL.md body

3. **For Workflows**: Create `.md` files in `.clinerules/workflows/`
   - Each workflow is a Markdown file with numbered steps
   - Filename becomes the command (e.g., `deploy.md` invoked with `/deploy.md`)
   - Steps can be high-level (natural language) or specific (XML tool syntax)
   - Can combine natural language, Cline tools, CLI tools, and MCP tools
   - Use `ask_followup_question` for user input
   - Include error handling instructions
   - Keep workflows focused on a single purpose

## Version Control

Both `.clinerules/` and `.cline/skills/` are tracked in version control, ensuring the entire team uses the same standards and guidance.

## Resources

- Cline Rules Documentation: https://docs.cline.bot/customization/cline-rules
- Cline Skills Documentation: https://docs.cline.bot/customization/skills
- Cline Workflows Documentation: https://docs.cline.bot/customization/workflows
- Project Rules: See `RULES.md` in project root

## Quick Reference

### Invoking Workflows
Type `/` in the chat input to see available workflows. Examples:
- `/create-mcp-ability.md` - Create a new MCP ability
- `/test-mcp-abilities.md` - Test existing abilities
- `/release-preparation.md` - Prepare a release
- `/debug-learndash.md` - Debug LearnDash issues
- `/setup-project.md` - Set up development environment

### Skill Activation
Skills activate automatically when Cline detects relevant keywords:
- "LearnDash", "courses", "lessons" → learndash-integration skill
- "MCP ability", "register ability" → mcp-ability-development skill
