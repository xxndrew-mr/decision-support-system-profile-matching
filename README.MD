# 🧑‍💼 Decision Support System for Employee Recruitment

## 📌 Project Overview
This project is a **Decision Support System (DSS)** developed to assist the recruitment process at **Toko Dwi Bhakti Offset**.  
The system uses the **Profile Matching method**, which compares candidate profiles against predefined job criteria to generate a ranking of the most suitable applicants.

The application is built as a **web-based system** with different access levels for HR and the Owner.

---

## ✨ Features
- 🔐 **Login system** for HR and Owner  
- 📋 **Candidate management** (add, view, and evaluate candidates)  
- ⚖️ **Profile Matching algorithm** for calculating candidate scores  
- 📊 **Ranking system** to determine the best candidates based on defined criteria and weights  
- 👤 **User profile management**  
- 📑 **Reports & results export**  

---

## 🛠️ Tech Stack
- **Frontend**: HTML, CSS, Bootstrap  
- **Backend**: PHP (Native PHP)  
- **Database**: MySQL  
- **Environment**: XAMPP (local deployment), phpMyAdmin  

---

## 🧮 Profile Matching Method
Steps used in this system:
1. Define **criteria and sub-criteria** (e.g., education, experience, technical skills, communication, physical condition).  
2. Assign **weights** to each criterion (core factor vs. secondary factor).  
3. Calculate **gap values** between candidate attributes and the ideal profile.  
4. Generate a **final ranking score** to identify the most suitable candidate.  

