# Lapin-Cove
A hotel for the Yrgopelago assignment, This webbsite is to book rooms (only for January 2025) and additional features for a fictional hotel. 

## SOme of the requrements from the teacher

Below you'll find a list of requirements which need to be fulfilled in order to complete the project.

- The application should be developed using HTML, CSS, SQL and PHP. Add a bit of javaScript if needed.

- Only desktop. No mobile.

- The application should be using a SQL (sqlite, MySQL etc) database.

- The application should be pushed to a repository on GitHub. Please enter the url in your `yrgopelago.md` in your feedback/grade-repo. If private, you should invite `hassehulabeck` as collaborator. 

- The project should declare strict types in files containing only PHP code.

- The project should not include any coding errors, warning or notices.

- The repository should have at least 20 commits and you have to commit at least once every time you're working on the project.

- The repository must contain a `README.md` file with a description of the project and possibly instructions for installation (if needed).

- The repository must contain a LICENSE file.

- You must follow the four hotel rules below

- The project must receive a code review by another student. Add at least 7 comments to the student's `README.md` file through a pull request. Give feedback to the student below your name. The last student gives feedback to the first student in the list. Add your feedback after lunch at january 10th. A code review line could look like this:

```bash
example.js:10-21 - Your solution seems to be working, but instead of these rows, use the built-in function trim() instead.
```

- Every hotel has exactly three single rooms (budget, standard and luxury), so you can only have three guests at the same time.

- As a manager, you set the price for your three rooms, but you should probably adjust the price according to the room standard and the star rating of the hotel. The more stars, the higher the cost.

- The hotel website must have a form where visitors can book a room.

- Your hotel MUST give a response to every succesful booking. The response should be in json and MUST have the following properties:

  - island
  - hotel
  - arrival_date
  - departure_date
  - total_cost
  - stars
  - features
  - additional_info. (This last property is where you can put in a personal greeting from your hotel, an image URL or whatever you like.)



## The stars

&star; The hotel website has a graphical presentation of the availibility of the three rooms. (_There's some nice packages that can simplify that part. Try to google php package calendar_)

&star; The hotel can give discounts, for example, how about 30% off for a visit longer than three days?

&star; The hotel can offer at least three features that a visitor can pay for. You can create your own features, but checkout the different features that are listed at [Awards - Points for the tourist](#awards--points-for-the-tourists), as they will be more valuable for the tourists. 

*Note: A hotel **cannot** offer all the features that makes an accepted set. (For example, your hotel cannot offer bicycle, unicycle and rollerblades).*

&star; The hotel has the ability to use external data (images, videos, text etc) when producing succesful booking responses that the customers get.

&star; The hotel manager has an `admin.php` page - accessible only by using your API_KEY - where different data can be altered, such as room prices, the star rating, discount levels and whatever you can think of.




#JESPERS CODE REVIEW

* It coould be beneficial to seperate the stylesheet in sections. That way it's easier to collaborators to easily find the style for each section.
* Nice comments on fuunctions in both php and javascript.
* Its a good practice to split the header/footer in different files and require in. Easier to manage if the website has alot of pages.
* I can't see if you sanitize your inputs before sending it away! on row 75 in booking.php. it could be dangerous if that is the cae (but what do I know!!!)
* IN your database. A table for discounts would make it easier to change values for what % discount you offer and what actions that are needed for the customer to get a GREAT DEAL.
* on row 101 in index.html you have started to set min and max values for your calendar, and you also have that on row 15 in your calendar.js. If it isn't as a "fallback" if one of them doesnt load, you only need one of them.
* $database = new PDO('sqlite:' . __DIR__ . '/Backend/LapinCove.db') The database connection is does lack error handlings. 
