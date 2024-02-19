<?php

namespace Controller;

use Model\Movie;
use Model\MovieManager;
use Model\Router;
use Model\UserManager as UserManager;
use \Exception as Exception;

class MovieController extends CommonController
{
    private MovieManager $movieManager;
    private Router $router;

    public function __construct()
    {
        parent::__construct();
        $this->movieManager = MovieManager::getInstance();
        $this->router = Router::getInstance();
    }

    /** GET
     *  Method serving the home route. Implements a list of films, search by author, search by title,
     * display status, add and import movies
     * @return void
     */
    public function home(): void
    {
        $query_value = '';
        $query_key = '';
        $movies = [];
        $message = '';
        $messageType = '';

        try {
            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $query_value = trim(@$_GET['query_value']);
                $query_key = trim(@$_GET['query_key']);
                $message = trim(@$_GET['message']);
                $messageType = trim(@$_GET['message_type']);

                if ($query_value && $query_key) {
                    $movies = $this->movieManager->queryMovies($query_key, $query_value);
                } else {
                    $movies = $this->movieManager->getMovies();
                }
            }
        } catch (Exception) {
            $message = 'Fatal error when get movies';
            $messageType = 'error';
        } finally {
            $this->getTemplate('MoviesTemplate', [
                'user' => $this->userManager->getCurrentUser(),
                'token' => $this->userManager->generateCsrfToken(),
                'movies' => $movies,
                'queryValue' => $query_value,
                'queryKey' => $query_key,
                'message' => $message,
                'messageType' => $messageType,
                'formats' => MovieManager::FORMAT_ENUMS,
                'styles' => ['main', 'movies'],
            ]);
        }
    }

    /** POST
     *  The method implements adding one movie to the repository.
     *  Only for registered users.
     *  For any result, redirects to the home page with status message and 301 code
     * @return void
     */
    public function movieAdd(): void
    {
        if (!$this->userManager->userCan('movie add')) {
            $this->exitWithError('Access denied', 403);
        }

        $params = ['message' => 'Error adding movie', 'message_type' => 'error'];
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->csrfCheck();

                $movie = Movie::FromArray([
                    'title' => $_POST['title'],
                    'release_year' => $_POST['year'],
                    'format' => $_POST['format'],
                    'actors' => @$_POST['actors'],
                    'description' => @$_POST['description'],
                ]);

                if ($this->movieManager->movieExists($movie->title, $movie->release_year)) {
                    $params = ['message' => "Movie '$movie->title'($movie->release_year) already exists", 'message_type' => 'info'];
                } elseif ($this->movieManager->addMovie($movie)) {
                    $params = ['message' => "Movie '$movie->title' added successful", 'message_type' => 'success'];
                }
            }
        } catch (Exception) {
            $params['message'] = 'Fatal error when adding movie';
        } finally { // back to Home with message
            $this->router->redirectToName('home', $params, 303);
        }
    }

    /** POST
     *  Imports movies from text file 'file'. For any status, redirection to
     * home page. Only for registered users.
     * @return void
     */
    public function movieImport(): void
    {
        if (!$this->userManager->userCan('movie add')) {
            $this->exitWithError('Access denied', 403);
        }

        $params = ['message' => 'Error importing movies', 'message_type' => 'error'];
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
                $this->csrfCheck();
                $file = @$_FILES['file'];

                if (@$file['error'] === UPLOAD_ERR_OK) {
                    $text = file_get_contents($file['tmp_name'] ?? '');
                    if ($text) {
                        $importResult = $this->movieManager->import($text);
                        $params = ['message' =>
                            "{$importResult['num_add']} movies added. " .
                            "{$importResult['num_skip']} movies skipped. " .
                            "{$importResult['num_error']} errors",
                            'message_type' => 'info'];
                    } else {
                        $params['message'] = 'File is empty';
                    }
                }
            }
        } catch (Exception) {
            $params['message'] = 'Fatal error when import movies';
        } finally {
            Router::getInstance()->redirectToName('home', $params, 301);
        }
    }

    /** POST
     *  Removes a movie from the repository by its id.
     *  Only for registered users.
     * @return void
     */
    public function movieDelete(): void
    {
        if (!$this->userManager->userCan('movie del')) {
            $this->exitWithError('Access denied', 403);
        }

        $params = ['message' => 'Movie not found', 'message_type' => 'error'];
        try {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $this->csrfCheck();

                $id = $_POST['movie_id'];
                $movieDeleted = $this->movieManager->removeMovie($id);
                if ($movieDeleted) {
                    $params = ['message' => 'Movie deleted', 'message_type' => 'success'];
                }
            }
        } catch (Exception) {
            $params['message'] = 'Fatal error when delete movie';
        } finally {
            Router::getInstance()->redirectToName('home', $params, 301);
        }
    }

    /** GET
     *  Shows detailed information about the movie.
     *  Does not require authentication.
     * @return void
     */
    public function movieDetails(): void
    {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $movie_id = $_GET['id'] ?? 0;

            $this->getTemplate('MovieDetailsTemplate', [
                'user' => $this->userManager->getCurrentUser(),
                'movie' => $this->movieManager->getMovie($movie_id),
                'styles' => ['main', 'movies'],
            ]);
        } else {
            $this->exitWithError('Method not supported', 405);
        }
    }
}