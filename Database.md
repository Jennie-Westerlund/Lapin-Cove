# This project uses SQLite and the name of the database-file is LapinCove.db

### SQL Queries to Recreate the Database

```sql
-- CREATE

-- Rooms
CREATE TABLE Rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room TEXT NOT NULL,
    price INTEGER NOT NULL
);

-- Features
CREATE TABLE Features (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    feature TEXT NOT NULL,
    price INTEGER NOT NULL
);

-- Bookings
CREATE TABLE Bookings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    room_id INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    FOREIGN KEY (room_id) REFERENCES Rooms(id)
);

-- Booking_Features (junction table)
CREATE TABLE Booking_Features (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id INTEGER NOT NULL,
    feature_id INTEGER NOT NULL,
    FOREIGN KEY (booking_id) REFERENCES Bookings(id),
    FOREIGN KEY (feature_id) REFERENCES Features(id)
);

-- INSERTS

-- Rooms
INSERT INTO Rooms (room, price) VALUES
('Lucie', 2),
('Amélie', 3),
('Celiné', 4);

-- Features
INSERT INTO Features (feature, price) VALUES
('Electric bicycle', 5),
('Champagne Bar', 3),
('Yatzy', 1);

```
