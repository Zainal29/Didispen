Kalau repositori itu punya temanmu, alurnya dibalik: kamu yang akan *push* ke branch baru milikmu (`fahri`) yang dibuat dari `main`, lalu temanmu yang akan meninjau kodenya sebelum dimasukkan ke `main`.

Langkah-langkah di terminal VS Code kamu:

1. **Buat & Pindah ke Branch Baru di Laptopmu:**
```bash
git checkout -b fahri

```


2. **Simpan & Commit Perubahan Kodenya:**
```bash
git add .
git commit -m "Update fitur dari fahri"

```


3. **Push Kode ke Branch `fahri` di GitHub Temanmu:**
```bash
git push -u origin fahri

```



Setelah kamu *push*, buka repositori di web GitHub milik temanmu. Di sana akan muncul tombol **Compare & pull request**. Kamu tinggal klik tombol tersebut untuk mengirim Pull Request ke branch `main`, sehingga temanmu bisa memeriksa kodenya terlebih dahulu sebelum di-*merge*.
