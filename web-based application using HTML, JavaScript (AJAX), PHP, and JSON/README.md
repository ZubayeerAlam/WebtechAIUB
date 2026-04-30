# Student Directory — AJAX + PHP + JSON

A minimal web app that fetches student data from a PHP backend using AJAX,
without any page reload.

---

## Files

| File           | Role                                                      |
|----------------|-----------------------------------------------------------|
| `index.html`   | Frontend — button, AJAX logic, dynamic card rendering     |
| `students.php` | Backend — builds student array, outputs JSON response     |

---

## How to Run

You need a local PHP server. Three easy options:

### Option 1 — PHP built-in server (recommended, no install needed)

```bash
# Inside the project folder
php -S localhost:8000
```
Then open: http://localhost:8000/index.html

---

### Option 2 — XAMPP / WAMP / MAMP

1. Copy both files into your `htdocs` (XAMPP) or `www` (WAMP) folder.
2. Start Apache from the control panel.
3. Open: http://localhost/index.html

---

### Option 3 — VS Code + PHP Server extension

Install the **PHP Server** extension, right-click `index.html` → "PHP Server: Serve project".

---

## How It Works

```
[Click Button]
      │
      ▼
  XMLHttpRequest  ──GET──►  students.php
                                  │
                           Build PHP array
                           json_encode()
                                  │
                  ◄──JSON──  echo response
      │
  JSON.parse()
      │
  Render cards
```

1. `index.html` — button click calls `fetchStudents()`
2. An `XMLHttpRequest` sends a `GET` to `students.php`
3. PHP constructs a student array and returns it as JSON (`Content-Type: application/json`)
4. JavaScript parses the response with `JSON.parse()` and renders student cards
5. CGPA bars animate into view; raw JSON is available in a collapsible panel

---

## Customising Students

Edit the `$students` array in `students.php`:

```php
[
    "id"         => "CSE-0001",
    "name"       => "Your Student",
    "department" => "CSE",
    "semester"   => "1st",
    "cgpa"       => 3.90
],
```

Save the file — the next button click will fetch the updated list instantly.
