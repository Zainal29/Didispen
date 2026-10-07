Kalau repositori itu punya temanmu, alurnya dibalik: kamu yang akan *push* ke branch baru milikmu (`fahri`) yang dibuat dari `main`, lalu temanmu yang akan meninjau kodenya sebelum dimasukkan ke `main`.

Langkah-langkah di terminal VS Code kamu:

1. **Buat & Pindah ke Branch Baru di Laptopmu:**
```bash
git checkout -b Sabrian

```


2. **Simpan & Commit Perubahan Kodenya:**
```bash
git add (file seng mok ganti ataau seng mok gawe )

git commit -m "Update fitur dari Sabrian"

# iku bagian git commit -m "" isine sembarang sesuai karo seng mok ganti misal git commit -m "ganti bagian admin"

```


3. **Push Kode ke Branch `fahri` di GitHub Temanmu:**
```bash
git push -u origin Sabrian

```



Setelah kamu *push*, buka repositori di web GitHub milik temanmu. Di sana akan muncul tombol **Compare & pull request**. Kamu tinggal klik tombol tersebut untuk mengirim Pull Request ke branch `main`, sehingga temanmu bisa memeriksa kodenya terlebih dahulu sebelum di-*merge*.
nnitip