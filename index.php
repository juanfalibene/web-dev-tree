<?php
    get_header();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Web Dev Tree</title>
  </head>
  <body>
    <header>
      <h1 class="title">🌐 🤓 🌲</h1>
      <p class="description">Resources for learning web development</p>
      <nav>
        <ul class="nav-links">
          <li class="nav-link"><a href="#">Read more</a></li>
          <li class="nav-link"><a href="#">GitHub</a></li>
          <li class="nav-link">
            <a href="#">Add Link</a>
          </li>
        </ul>
      </nav>
    </header>

    <main class="container">
      <section class="section">
        <ul class="categories">
          <li class="category" id="all">
            <a href="#">All</a>
          </li>
          <li class="category" id="docs">
            <a href="#">Docs</a>
          </li>
          <li class="category" id="inspiration">
            <a href="#">Inspiration</a>
          </li>
          <li class="category" id="voices">
            <a href="#">Voices</a>
          </li>
          <li class="category" id="news">
            <a href="#">News</a>
          </li>
          <li class="category" id="tools">
            <a href="#">Tools</a>
          </li>
          <li class="category" id="ui">
            <a href="#">UI</a>
          </li>
          <li class="category" id="ux">
            <a href="#">UX</a>
          </li>
          <li class="category" id="design">
            <a href="#">Design</a>
          </li>
          <li class="category" id="code">
            <a href="#">Code</a>
          </li>
          <li class="category" id="playlists">
            <a href="#">Playlists</a>
          </li>
          <li class="category" id="tutorials">
            <a href="#">Tutorials</a>
          </li>
          <li class="category" id="newsletters">
            <a href="#">Newsletters</a>
          </li>
        </ul>

        <ul class="tree">
          <li class="node docs active">
            <a href="html/index.html" rel="noopener noreferrer" target="_blank"
              >Docs 01<span>Description</span></a
            >
          </li>
          <li class="node docs">
            <a href="html/index.html" rel="noopener noreferrer" target="_blank"
              >Docs 02<span>Description</span></a
            >
          </li>
          <li class="node inspiration">
            <a href="css/index.html" rel="noopener noreferrer" target="_blank"
              >Ins 01</a
            >
          </li>
          <li class="node voices">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Voices 01</a
            >
          </li>
          <li class="node news">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >News 01</a
            >
          </li>
          <li class="node tools">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Tools 01</a
            >
          </li>
          <li class="node ui">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >UI 01</a
            >
          </li>
          <li class="node ux">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >UX 01</a
            >
          </li>
          <li class="node design">
            <a href="javascript/index.html">Design 01</a>
          </li>
          <li class="node code">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Code 01</a
            >
          </li>
          <li class="node tutorials">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Tutorials 01</a
            >
          </li>
          <li class="node newsletters">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Newsletters 01</a
            >
          </li>
          <li class="node playlists">
            <a
              href="javascript/index.html"
              rel="noopener noreferrer"
              target="_blank"
              >Playlists 01</a
            >
          </li>
        </ul>
      </section>
      <?php
        get_footer();
      ?>
      <footer>
        <a
          href="https://juanfalibene.com"
          target="_blank"
          rel="noopener noreferrer"
        >
          <img
            src="http://localhost:3000/wp-content/themes/webdevtree/images/juanfalibene_profile.svg"
            class="img-profile"
          />juanfalibene.com</a
        >
      </footer>
    </main>
  </body>
</html>
