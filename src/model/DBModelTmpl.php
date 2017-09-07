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
        
        try {
            $queryResult = $this->db->query('SELECT * FROM book ORDER BY id');
        } catch (PDOException $ex) {
            throw $ex;
        }

        if ($queryResult) {
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

        if (!is_numeric($id)) {
            throw new Exception("invalid id");
        }

        try {
            $row = $this->db->query('SELECT * FROM book WHERE id=' . $id)->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            throw $ex;
        }

        if ($row == null) {
            return null;
        } else {
            return new Book($row['title'], 
                    $row['author'],
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

        if ($book->title == "" || $book->author == "") {
            throw new Exception("missing title or author");
        }

        $bookDescription = $book->description;
        if ($bookDescription == "") {
            $bookDescription = null;
        }

        // TODO alternative solution to this (without reference): make unit 
        //  test pick out book from the model instead of from the .....
        $stmt = $this->db->prepare("INSERT INTO book VALUES (null, :title, :author, :description)");
        $stmt->bindValue(':title', $book->title);
        $stmt->bindValue(':author', $book->author);
        $stmt->bindValue(':description', $bookDescription);

        try {
            $stmt->execute();
        } catch (PDOException $ex) {
            throw $ex;
        }

        $book->id = $this->db->lastInsertId();
    }

    /** Modifies data related to a book in the collection.
     * @param $book Book The book data to be kept.
     * @todo Implement function using PDO and a real database.
     */
    public function modifyBook($book)
    {
        if ($book->title == "" || $book->author == "") {
            throw new Exception("missing title or author");
        }

        $bookDescription = $book->description;
        if ($bookDescription == "") {
            $bookDescription = null;
        }
        
        $stmt = $this->db->prepare('UPDATE book SET title=:title, author=:author, description=:description WHERE id=:id');
        $stmt->bindValue(':title', $book->title);
        $stmt->bindValue(':author', $book->author);
        $stmt->bindValue(':description', $bookDescription);
        $stmt->bindValue(':id', $book->id);
        $stmt->execute();
    }

    /** Deletes data related to a book from the collection.
     * @param $id integer The id of the book that should be removed from the collection.
     */
    public function deleteBook($id)
    {
        $affected_rows = $this->db->exec("DELETE FROM book WHERE id=$id");
    }
	
}

?>
