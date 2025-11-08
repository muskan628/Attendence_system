import pandas as pd
import mysql.connector 

# MySQL connection 
conn = mysql.connector.connect(
    host='localhost',
    user='root',
    password='',     
    database='attendence_db'
)
cursor = conn.cursor()

# Excel file- Teachers data
data = pd.read_excel('')

# desired columns 
data = data[['id', 'name', 'gender', 'age', 'category', 'religion', 'state', 'city', 'email', 'phone', 'address']]  

# Table 
cursor.execute("""
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY,
    name VARCHAR(50),
    gender VARCHAR(10),
    age INT,
    category VARCHAR(50),
    religion VARCHAR(50),
    state VARCHAR(50),
    city VARCHAR(50),
    email VARCHAR(50),
    phone INT,
    address VARCHAR(100)
)
""")

# Excel columns koinserted into MySQL 
for i, row in data.iterrows():
    sql = '''INSERT INTO teachers (id, name, gender,age, category, religion, state, city, email, phone, address) VALUES (%s, %s, %s,%s, %s, %s,%s, %s, %s, %s, %s)'''
    cursor.execute(sql, tuple(row))

conn.commit()
print("Selected columns inserted successfully!")

conn.close()
