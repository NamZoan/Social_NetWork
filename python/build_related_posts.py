#!/usr/bin/env python
# -*- coding: utf-8 -*-

"""
Script sinh dữ liệu cho bảng related_posts bằng TF-IDF + cosine similarity.

Bước chạy:
1. Chỉnh DB_CONFIG cho đúng database của bạn.
2. python build_related_posts.py
"""

import pymysql
import pandas as pd
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import linear_kernel
from tqdm import tqdm

# ================== CONFIG ==================

DB_CONFIG = {
    "host": "127.0.0.1",
    "user": "root",
    "password": "",           # chỉnh lại
    "database": "doan",       # chỉnh lại theo tên DB của bạn
    "charset": "utf8mb4"
}

TOP_K = 10            # mỗi post lấy top K bài giống nhất
MIN_SIMILARITY = 0.4  # ngưỡng tối thiểu, dưới thì bỏ qua

# ============================================


def get_connection():
    return pymysql.connect(
        host=DB_CONFIG["host"],
        user=DB_CONFIG["user"],
        password=DB_CONFIG["password"],
        database=DB_CONFIG["database"],
        charset=DB_CONFIG["charset"],
        cursorclass=pymysql.cursors.DictCursor
    )


def fetch_posts():
    """
    Lấy danh sách post từ DB.
    Ở đây mình chỉ lấy post public và content khác rỗng.
    """
    conn = get_connection()
    try:
        with conn.cursor() as cur:
            sql = """
                SELECT id, content
                FROM posts
                WHERE privacy_setting = 'public'
                  AND content IS NOT NULL
                  AND TRIM(content) <> ''
            """
            cur.execute(sql)
            rows = cur.fetchall()
            df = pd.DataFrame(rows)
            return df
    finally:
        conn.close()


def clear_related_posts():
    """
    Xóa dữ liệu cũ trong bảng related_posts.
    Nếu muốn giữ lại thì comment hàm này.
    """
    conn = get_connection()
    try:
        with conn.cursor() as cur:
            cur.execute("TRUNCATE TABLE related_posts")
        conn.commit()
        print("Đã TRUNCATE bảng related_posts.")
    finally:
        conn.close()


def insert_related_posts(related_records):
    """
    Insert nhiều bản ghi vào bảng related_posts.
    related_records: list[tuple(post_id, related_post_id, similarity_score)]
    """
    if not related_records:
        return

    conn = get_connection()
    try:
        with conn.cursor() as cur:
            sql = """
                INSERT INTO related_posts (post_id, related_post_id, similarity_score, created_at)
                VALUES (%s, %s, %s, NOW())
            """
            cur.executemany(sql, related_records)
        conn.commit()
    finally:
        conn.close()


def build_tfidf_matrix(contents):
    """
    Tạo TF-IDF matrix từ danh sách content.
    """
    vectorizer = TfidfVectorizer(
        max_features=5000,        # giới hạn số feature cho nhẹ
        ngram_range=(1, 2),       # unigram + bigram
        # stop_words=None        # nếu có stopword tiếng Việt thì thêm vào
    )
    tfidf_matrix = vectorizer.fit_transform(contents)
    return tfidf_matrix


def main():
    print("Đang lấy posts từ database...")
    posts_df = fetch_posts()

    if posts_df.empty:
        print("Không có post nào để xử lý.")
        return

    print(f"Đã lấy {len(posts_df)} posts.")

    # Build TF-IDF
    print("Đang build TF-IDF matrix...")
    tfidf_matrix = build_tfidf_matrix(posts_df["content"].tolist())

    print("Đang tính cosine similarity (có thể hơi lâu nếu nhiều post)...")
    # linear_kernel(tfidf, tfidf) ~ cosine_similarity nhưng nhanh hơn cho TF-IDF
    cosine_sim = linear_kernel(tfidf_matrix, tfidf_matrix)

    # Xóa dữ liệu cũ
    clear_related_posts()

    # Map index -> post_id
    post_ids = posts_df["id"].tolist()

    all_related_records = []

    print("Đang tìm top K bài giống nhau cho từng post...")
    for idx, post_id in tqdm(list(enumerate(post_ids))):
        # similarity vector cho post idx
        sim_scores = list(enumerate(cosine_sim[idx]))

        # sort theo score giảm dần, bỏ chính nó (idx == idx)
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)

        # Lấy top K bài khác nó, có similarity >= MIN_SIMILARITY
        count = 0
        for j, score in sim_scores:
            if j == idx:
                continue  # bỏ chính nó
            if score < MIN_SIMILARITY:
                break      # các bài sau sẽ càng thấp, nên break
            related_post_id = post_ids[j]
            all_related_records.append((post_id, related_post_id, float(score)))
            count += 1
            if count >= TOP_K:
                break

    print(f"Tổng số bản ghi related_posts chuẩn bị insert: {len(all_related_records)}")

    # Chia batch cho an toàn (tránh insert 1 lần quá to)
    BATCH_SIZE = 1000
    for i in range(0, len(all_related_records), BATCH_SIZE):
        batch = all_related_records[i:i + BATCH_SIZE]
        insert_related_posts(batch)

    print("Hoàn thành! Bảng related_posts đã được cập nhật.")


if __name__ == "__main__":
    main()
