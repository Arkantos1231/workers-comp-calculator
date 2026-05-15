# Workers' Comp Calculator Plugin

A WordPress plugin for calculating Workers' Compensation claim values with state-specific routing and mobile-responsive forms.

## Installation

1. Upload the `workers-comp-calculator` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. The plugin will automatically flush rewrite rules on activation

## Usage

### Custom Post Type

The plugin creates a custom post type **"Workers' Comp Calculators"** in WordPress admin.

1. Go to **Workers' Comp Calculators → Add New**
2. Enter a title (e.g., "Maryland Workers' Comp Calculator")
3. Select the state in the "State Information" meta box
4. Publish the post
5. The URL will be automatically generated from the title: `/tools/workers_comp_calculator/maryland-workers-comp-calculator/`

The calculator form automatically appears on CPT posts.

### Shortcode (Alternative)

You can also use the shortcode on any page or post:

```
[workers_comp_calculator]
```

### Dynamic URLs

URLs are automatically generated from post titles:
- Title: "Maryland Workers' Comp Calculator" → URL: `/tools/workers_comp_calculator/maryland-workers-comp-calculator/`
- Title: "Pennsylvania Calculator" → URL: `/tools/workers_comp_calculator/pennsylvania-calculator/`

The state is automatically detected from the post's meta field or title and pre-filled in the form.

## File Structure

```
workers-comp-calculator/
├── workers-comp-calculator.php    # Main plugin file
├── includes/
│   ├── class-wcc-routing.php      # URL routing and state detection
│   ├── class-wcc-form-handler.php # Form processing and AJAX
│   └── class-wcc-post-type.php   # Custom post type registration
├── templates/
│   └── calculator-form.php        # Form template
├── assets/
│   ├── css/
│   │   └── calculator.css         # Mobile-first styles
│   └── js/
│       └── calculator.js          # Form logic and validation
├── README.md
└── CUSTOM-POST-TYPE-GUIDE.md      # Detailed CPT usage guide
```

## Features

- ✅ Custom Post Type for managing state calculators
- ✅ Dynamic URLs based on post title
- ✅ Mobile-responsive form design
- ✅ Conditional field logic (occupational disease)
- ✅ Body part autocomplete/search
- ✅ Form validation (client and server-side)
- ✅ Contact capture before showing results
- ✅ AJAX form submission
- ✅ Placeholder calculation (ready for knowledge base integration)
- ✅ State information meta box in admin

## Next Steps

1. Add knowledge base system for state-specific calculations
2. Integrate GoHighLevel CRM webhook
3. Add calendar booking integration
4. Implement SEO schema markup
5. Add admin settings panel

## Code Style

The code is written to be simple and easy to understand:
- Clear function names
- Extensive comments
- Straightforward logic
- No unnecessary complexity

