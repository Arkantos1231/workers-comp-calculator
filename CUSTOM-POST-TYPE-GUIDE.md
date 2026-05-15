# Custom Post Type Guide

## How to Use the State Calculator Custom Post Type

The plugin creates a custom post type called **"Workers' Comp Calculators"** that allows you to manually create calculator pages for each state.

## Creating a New State Calculator Page

### Step 1: Go to Admin Menu

1. In WordPress admin, look for **"Workers' Comp Calculators"** in the left menu
2. Click **"Add New"**

### Step 2: Fill in the Post Details

1. **Title**: Enter the title (e.g., "Maryland Workers' Comp Calculator")
   - The URL will be automatically generated from the title
   - Example: Title "Maryland Workers' Comp Calculator" → URL `/tools/workers_comp_calculator/maryland-workers-comp-calculator/`

2. **Content** (Optional): 
   - You can add content above the calculator if needed
   - The calculator will automatically appear below your content
   - Or you can manually add `[workers_comp_calculator]` shortcode anywhere

3. **Featured Image** (Optional): Add a state-specific image

4. **Excerpt** (Optional): Add a short description for SEO

### Step 3: Set State Information

In the **"State Information"** meta box (right sidebar):

1. **State Name**: Select the state from the dropdown
   - This helps the calculator identify which state's rules to use
   - Example: Select "Maryland"

2. **State Code** (Optional): Enter the two-letter code
   - Example: "MD" for Maryland
   - This is just for reference

3. **URL Preview**: Shows what the final URL will be
   - Format: `/tools/workers_comp_calculator/{state}-workers-comp-calculator/`
   - State name is dynamic, prefix and suffix are static
   - Updates when you select a state

### Step 4: Publish

Click **"Publish"** to make the calculator page live.

## URL Structure

The URL structure follows this pattern:

**Format**: `/tools/workers_comp_calculator/{state}-workers-comp-calculator/`

- **Static prefix**: `/tools/workers_comp_calculator/`
- **Dynamic part**: State name (extracted from title or meta field)
- **Static suffix**: `-workers-comp-calculator/`

**Examples:**
- **Title**: "Maryland Workers' Comp Calculator" → **URL**: `/tools/workers_comp_calculator/maryland-workers-comp-calculator/`
- **Title**: "Pennsylvania Calculator" → **URL**: `/tools/workers_comp_calculator/pennsylvania-workers-comp-calculator/`
- **Title**: "New York Workers Comp" → **URL**: `/tools/workers_comp_calculator/new-york-workers-comp-calculator/`

The state name is extracted from:
1. The "State Name" dropdown in the meta box (preferred)
2. Or automatically extracted from the post title if it contains a state name

The state name is converted to a slug (lowercase, spaces to hyphens) and appended with `-workers-comp-calculator`.

## How It Works

1. **Custom Post Type**: Creates a new content type specifically for calculators
2. **URL Structure**: URLs follow the pattern `/tools/workers_comp_calculator/{state}-workers-comp-calculator/`
   - State name is extracted and converted to slug
   - Static prefix: `/tools/workers_comp_calculator/`
   - Static suffix: `-workers-comp-calculator/`
3. **State Detection**: The calculator automatically detects the state from:
   - The "State Name" dropdown in meta box (preferred)
   - Or automatically extracts it from the post title if it contains a state name
4. **Auto-Insert Calculator**: The calculator form automatically appears on CPT posts (unless you manually add the shortcode)

## Managing Calculators

### View All Calculators

Go to **Workers' Comp Calculators → All Calculators** to see all your state calculator pages.

### Edit a Calculator

1. Go to **Workers' Comp Calculators → All Calculators**
2. Click on the calculator you want to edit
3. Make your changes
4. Click **"Update"**

### Delete a Calculator

1. Go to **Workers' Comp Calculators → All Calculators**
2. Hover over the calculator
3. Click **"Trash"**

## Tips

- **SEO-Friendly Titles**: Use descriptive titles like "{State} Workers' Comp Calculator"
- **Add Content**: You can add state-specific information above the calculator
- **Customize Per State**: Each calculator can have unique content, images, and settings
- **No Limits**: Create as many calculator pages as you need

## Example Workflow

1. Create "Maryland Workers' Comp Calculator" post
2. Select "Maryland" in State Information meta box
3. Add some introductory content about Maryland workers' comp
4. Publish
5. URL automatically becomes: `/tools/workers_comp_calculator/maryland-workers-comp-calculator/`
6. Calculator automatically appears on the page

**Note**: The URL will always be `/tools/workers_comp_calculator/{state}-workers-comp-calculator/` - only the state name changes.

That's it! Simple and flexible.

