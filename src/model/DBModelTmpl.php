<?php
include_once("IModel.php");
include_once("Book.php");

/** The Model is the class holding data about a collection of books. 
 * @author Rune Hjelsvold
 * @see http://php-html.net/tutorials/model-view-controller-in-php/ The tutorial code used as basis.
 */
class DBModel implements IModel
{        
    /**
      * The PDO object for interfacing the database
      *
      */
    protected $db = null;  
    
    /**
	 * @throws PDOException
     */
    public function __construct($db = null)  
    {  
	    if ($db) 
		{
			$this->db = $db;
		}
		else
		{
            // Create PDO connection
            try {
                $this->db = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4', 'root', '', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            } catch(PDOException $ex) {
                throw $ex; 
            }
		}
    }
    
    /** Function returning the complete list of books in the collection. Books are
     * returned in order of id.
     * TODO IN ORDER OF ID!
     * @return Book[] An array of book objects indexed and ordered by their id.
	 * @throws PDOException
     */
    public function getBookList()
    {
		$booklist = array();
        
        // tries to run query to fetch books
        $queryResult = null;
        try {
            $queryResult = $this->db->query('SELECT * FROM book ORDER BY id');
        } catch (PDOException $ex) {
            throw $ex;
        }

        // if we got a result back, process it. if not, throw
        if ($queryResult) {
    
            // for each row in the result, make a Book out of it and add it
            // to $booklist
            foreach ($queryResult as $row) {
                array_push($booklist, new Book($row['title'], $row['author'], 
                        $row['description'], $row['id']));
            }
        } else {
            throw new Exception("Couldn't get books from database (getBookList");
        }

        return $booklist;
    }
    
    /** Function retrieving information about a given book in the collection.
     * @param integer $id the id of the book to be retrieved
     * @return Book|null The book matching the $id exists in the collection; null otherwise.
	 * @throws PDOException, Exception
     */
    public function getBookById($id)
    {

        // if $id isn't a number, throw
        if (!is_numeric($id)) {
            throw new Exception("invalid id");
        }

        // try to fetch row from database
        $row = null;
        try {
            $row = $this->db->query('SELECT * FROM book WHERE id=' . $id)->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            throw $ex;
        }

        // return null if none found. make new Book if was found
        if ($row == null) {
            return null;
        } else {
            return new Book($row['title'],      // make new Book out of 
                    $row['author'],             // row and return
                    $row['description'],
                    $row['id']);
        }
    }
    
    /** Adds a new book to the collection.
     * @param $book Book The book to be added - the id of the book will be set after successful insertion.
	 * @throws PDOException
     */
    public function addBook($book) 
    {
        // if invalid input, throw
        if ($book->title == "" || $book->author == "") {
            throw new Exception("missing title or/and author");
        }

        // convert empty description to null
        $bookDescription = $book->description;
        if ($bookDescription == "") {
            $bookDescription = null;
        }

        // build insertion-query from $book
        $stmt = $this->db->prepare("INSERT INTO book VALUES (null, :title, :author, :description)");
        $stmt->bindValue(':title', $book->title);
        $stmt->bindValue(':author', $book->author);
        $stmt->bindValue(':description', $bookDescription);

        // try to run the query
        try {
            $stmt->execute();
        } catch (PDOException $ex) {
            throw $ex;
        }

        // update the $book object with it's id in the DB
        $book->id = $this->db->lastInsertId();
    }

    /** Modifies data related to a book in the collection.
     * @param $book Book The book data to be kept.
     * @todo Implement function using PDO and a real database.
     */
    public function modifyBook($book)
    {
        // if invalid input: throw
        if ($book->title == "" || $book->author == "") {
            throw new Exception("missing title or author");
        }

        // convert empty description to null
        $bookDescription = $book->description;
        if ($bookDescription == "") {
            $bookDescription = null;
        }
        
        // prepare statement with information from $book
        $stmt = $this->db->prepare('UPDATE book SET title=:title, author=:author, description=:description WHERE id=:id');
        $stmt->bindValue(':title', $book->title);
        $stmt->bindValue(':author', $book->author);
        $stmt->bindValue(':description', $bookDescription);
        $stmt->bindValue(':id', $book->id);

        // try to run the query
        try {
            $stmt->execute();
        } catch (PDOException $ex) {
            throw $ex;
        }
    }

    /** Deletes data related to a book from the collection.
     * @param $id integer The id of the book that should be removed from the collection.
     */
    public function deleteBook($id)
    {
        // delete the book. (we trust the $id)
        $this->db->exec("DELETE FROM book WHERE id=$id");
    }
	
}

?>
