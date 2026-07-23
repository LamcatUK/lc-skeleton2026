#!/bin/bash
set -e

# One-time bootstrap: run this straight after checking out and renaming the
# skeleton directory for a new project. Renames every lc-skeleton2026 /
# LC Skeleton 2026 / lc_skeleton_ / LC_SKELETON_ / LC_Skeleton_ reference to
# your new theme, then resets git to a fresh history so this project doesn't
# drag the skeleton's own commit log around. Leaves GitHub itself to you
# (gh repo create + push) — deliberately not automated, see README.

old_slug="lc-skeleton2026"
old_name="LC Skeleton 2026"
old_prefix="lc_skeleton"
old_prefix_upper="LC_SKELETON"
old_prefix_pascal="LC_Skeleton"
old_nav_walker_file="inc/class-lc-skeleton-nav-walker.php"

# Idempotency guard — refuse to run twice against an already-renamed project.
if ! grep -rq "$old_slug" style.css 2>/dev/null; then
  echo "No '$old_slug' references found in style.css — this looks like it's already been renamed."
  echo "Refusing to run again (would double-rename). Delete setup.sh if you don't need it anymore."
  exit 1
fi

# Refuse to run on a dirty tree — this is meant to run on a fresh checkout,
# not part-way through real work.
if [ -d .git ] && [ -n "$(git status --short 2>/dev/null)" ]; then
  echo "Working tree has uncommitted changes. setup.sh resets git history entirely —"
  echo "commit, stash, or discard first, then re-run."
  exit 1
fi

read -p "New theme name (e.g. \"LC New Client 2026\"): " new_name
if [ -z "$new_name" ]; then
  echo "No theme name provided."
  exit 1
fi

# Suggest a slug from the name, but name and slug often want to diverge
# (a cleaner/shorter slug than the marketing name) — offer it as a default,
# not a fait accompli.
suggested_slug=$(echo "$new_name" | tr '[:upper:]' '[:lower:]' | tr ' ' '-')
read -p "Theme slug [$suggested_slug]: " slug_input
new_slug="${slug_input:-$suggested_slug}"

# Lowercase/uppercase PHP fn/const prefixes derived from the *confirmed*
# slug, not the raw name, so they can't silently diverge from it.
new_prefix=$(echo "$new_slug" | tr '-' '_')
new_prefix_upper=$(echo "$new_prefix" | tr '[:lower:]' '[:upper:]')

# The one class name (LC_Skeleton_Nav_Walker) uses mixed case, so it's
# derived from the name instead, word by word — already-uppercase words
# (acronyms like "LC") are preserved as typed rather than forced to "Lc".
new_prefix_pascal=""
for word in $new_name; do
  if [[ "$word" =~ ^[A-Z0-9]+$ ]]; then
    part="$word"
  else
    part="$(echo "${word:0:1}" | tr '[:lower:]' '[:upper:]')$(echo "${word:1}" | tr '[:upper:]' '[:lower:]')"
  fi
  new_prefix_pascal="${new_prefix_pascal:+${new_prefix_pascal}_}${part}"
done

# WPCS (WordPress.Files.FileName.InvalidClassFileName) requires a class's
# filename to be "class-" + the full class name in kebab-case — since the
# nav walker's class name changes per project, its filename has to change
# with it, not just its contents.
new_nav_walker_slug=$(echo "$new_prefix_pascal" | tr '[:upper:]' '[:lower:]' | tr '_' '-')
new_nav_walker_file="inc/class-${new_nav_walker_slug}-nav-walker.php"

echo ""
echo "This will replace, across every tracked file:"
echo "  \"$old_name\"           -> \"$new_name\""
echo "  $old_slug               -> $new_slug"
echo "  ${old_prefix}_ (PHP fn/const)  -> ${new_prefix}_"
echo "  ${old_prefix_pascal}_ (class name)  -> ${new_prefix_pascal}_"
echo "  $old_nav_walker_file -> $new_nav_walker_file"
echo ""
echo "...then delete .git and start a fresh history (first commit only —"
echo "GitHub repo creation and push are left to you)."
echo ""
read -p "Proceed? (y/n): " confirm
if [ "$confirm" != "y" ] && [ "$confirm" != "Y" ]; then
  echo "Cancelled."
  exit 0
fi

# Text replacement — every tracked file, skipping binaries/build tooling
# that shouldn't be touched.
files=$(git ls-files 2>/dev/null || find . -type f -not -path "./node_modules/*" -not -path "./.git/*")

for f in $files; do
  [ -f "$f" ] || continue
  case "$f" in
    *.png|*.jpg|*.jpeg|*.gif|*.woff|*.woff2|*.map) continue ;;
  esac
  sed -i \
    -e "s/${old_name}/${new_name}/g" \
    -e "s/${old_slug}/${new_slug}/g" \
    -e "s/${old_prefix_upper}/${new_prefix_upper}/g" \
    -e "s/${old_prefix_pascal}/${new_prefix_pascal}/g" \
    -e "s/${old_prefix}/${new_prefix}/g" \
    "$f"
done

echo "Replaced references across $(echo "$files" | wc -l) files."

# Rename the nav walker file to match its now-renamed class (see above —
# WPCS requires this, and the content substitution above doesn't touch
# filenames).
if [ -f "$old_nav_walker_file" ]; then
  mv "$old_nav_walker_file" "$new_nav_walker_file"
  sed -i "s#${old_nav_walker_file}#${new_nav_walker_file}#g" functions.php
  echo "Renamed $old_nav_walker_file -> $new_nav_walker_file"
else
  echo "Warning: $old_nav_walker_file not found — skipping nav walker filename rename."
fi

# Reset git to a fresh history.
rm -rf .git
git init -q
git add -A
git commit -q -m "Initial commit from ${old_slug} skeleton"

echo ""
echo "Done. New theme: \"$new_name\" ($new_slug)."
echo ""
echo "Next steps:"
echo "  npm install"
echo "  gh repo create LamcatUK/$new_slug --public --source=. --push   # when you're ready"
