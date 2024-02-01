<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
  /**
   * Display a listing of the resource.
   */
  public function index(Request $request)
  {
    // displaying all the books
    $title = $request->input('title');
    $filter = $request->input('filter', '');

    $page = $request->has('page') ? $request->query('page') : 1;

    $books = Book::when(
      $title,
      fn ($query, $title) => $query->title($title)
    );

    // optionally do something with filtering or at least sort to the most recent one
    $books = match ($filter) {
      'popular_last_month' => $books->popularLastMonth(),
      'popular_last_6months' => $books->popularLast6Months(),
      'highest_rated_last_month' => $books->highestRatedLastMonth(),
      'highest_rated_last_6months' => $books->highestRatedLast6Months(),
      default => $books->latest()->withAvgRating()->withReviewsCount(),
    };
    // $books = $books->get(); replace by the below code

    // $books = $books->paginate(10);

    // tell caching mechanism under which key you wat to store some data + how long
    // BUT, if using this style, there will be additional step for correct user experienced[not Cache::remember which utilize use ...\Facade\Cache]
    // $books = cache()->remember('books', 3600, fn () => $books->get());
    // Do This
    // $cacheKey = 'books:' . $filter . ':' . $title;
    $cacheKey = 'books:' . $filter . ':' . $title . ':' . $page;
    $books =
      cache()->remember(
        $cacheKey,
        3600,
        fn () =>
        // $books->get()
        $books->paginate(10)
      );

    return view('books.index', ['books' => $books]);
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    // show the form page for creating a book
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // handle the create form
  }

  /**
   * Display the specified resource.
   */
  public function show(int $id)
  {
    // show one book
    $cacheKey = 'book:' . $id;

    $book = cache()->remember(
      $cacheKey,
      3600,
      fn () =>
      Book::with([ // using with instead load, with the way to fetch realtions together with the model at the same time
        'reviews' => fn ($query) => $query->latest()
      ])->withAvgRating()->withReviewsCount()->findOrFail($id)
    );

    return view('books.show', ['book' => $book]);
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(string $id)
  {
    // show the form for editing the book
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, string $id)
  {
    // handle the editing form
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(string $id)
  {
    // destroy/delete the book
  }
}
