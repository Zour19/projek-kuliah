<?php
// ==========================================
// LOGIKA NEWSLETTER
// ==========================================
if (isset($_POST['submit_newsletter'])) {
    $email_subs = db_escape($conn, $_POST['email_newsletter']);
    
    // Cek apakah email sudah berlangganan sebelumnya
    $cek_subs = db_query($conn, "SELECT id FROM subscribers WHERE email = '$email_subs'");
    if (db_num_rows($cek_subs) > 0) {
        echo "<script>alert('Email ini sudah berlangganan newsletter kami!');</script>";
    } else {
        db_query($conn, "INSERT INTO subscribers (email) VALUES ('$email_subs')");
        echo "<script>alert('Terima kasih telah berlangganan newsletter Matahari Florist!');</script>";
    }
}

// ==========================================
// QUERY ULASAN (Hanya yang di-ACC Admin)
// ==========================================
$q_reviews_footer = db_query($conn, "SELECT * FROM reviews WHERE status = 'approved' ORDER BY id DESC LIMIT 3");
?>

</main> 

    <section class="bg-white py-12 border-t border-brand-brown/10">
        <div class="max-w-7xl mx-auto px-4">
            <h3 class="text-2xl font-serif font-bold text-brand-brown mb-8 text-center">Ulasan Pelanggan</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <?php if ($q_reviews_footer && db_num_rows($q_reviews_footer) > 0): ?>
                    <?php while ($rev = db_fetch_assoc($q_reviews_footer)): ?>
                        <div class="bg-brand-light p-6 rounded-lg shadow-sm border border-brand-brown/5">
                            <div class="text-brand-yellow text-sm mb-2"><?= str_repeat('⭐', $rev['rating']); ?></div>
                            <p class="text-gray-600 text-sm italic mb-4">"<?= htmlspecialchars($rev['ulasan']); ?>"</p>
                            <p class="font-bold text-brand-brown text-sm">- <?= htmlspecialchars($rev['nama_pelanggan']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="col-span-3 text-center text-sm text-gray-500 italic">Belum ada ulasan pelanggan saat ini.</p>
                <?php endif; ?>
                
            </div>
            
            <!-- Tombol Beri Ulasan Tambahan (Sesuai Konteks Fitur) -->
            <div class="text-center mt-8">
                <a href="tulis_ulasan.php" class="inline-block border border-brand-brown text-brand-brown hover:bg-brand-brown hover:text-white text-sm font-bold py-2 px-6 rounded transition">
                    Tulis Ulasan Anda
                </a>
            </div>
        </div>
    </section>

    <footer class="bg-brand-light pt-12 pb-12 text-brand-brown border-t border-brand-brown/10 relative">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                
                <div>
                    <h4 class="font-bold text-lg mb-4">Social</h4>
                    <p class="text-sm mb-4 leading-relaxed">Stay current with updates from our social channels.</p>
                    <p class="text-sm mb-6 leading-relaxed">
                        Or contact us directly at <a href="https://wa.me/6282122490002" class="underline hover:text-brand-yellow transition">+6282122490002</a><br>
                        (WA chat/order)
                    </p>
                    <a href="https://www.instagram.com/matahariflorist_" target="_blank" aria-label="Instagram" class="inline-block hover:opacity-70 transition">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                </div>

                <div>
                    <h4 class="font-bold text-lg mb-4">Newsletter</h4>
                    <p class="text-sm mb-4 leading-relaxed">Subscribe to get special offers, free giveaways, and once-in-a-lifetime deals.</p>
                    <!-- Form Newsletter (Sudah ditambahkan POST & action) -->
                    <form method="POST" action="" class="flex w-full mt-4 border border-brand-brown/30 bg-transparent">
                        <input type="email" name="email_newsletter" placeholder="email@newsletter.com" class="px-3 py-2 text-sm flex-grow focus:outline-none bg-transparent text-brand-brown placeholder-brand-brown/60" required>
                        <button type="submit" name="submit_newsletter" class="bg-black text-white px-5 py-2 text-sm font-bold hover:bg-gray-800 transition">JOIN</button>
                    </form>
                </div>

                <div>
                    <h4 class="font-bold text-lg mb-4">Customer Care</h4>
                    <div class="text-sm space-y-4">
                        <div>
                            <p class="font-medium text-xs">Call</p>
                            <p>+62813-1199-6099</p>
                        </div>
                        <div>
                            <p class="font-medium text-xs">Email</p>
                            <p>admin@matahariflorist.com</p>
                        </div>
                        <div>
                            <p class="font-medium text-xs">WhatsApp</p>
                            <p>+62877-8312-1288</p>
                        </div>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-lg mb-4">Visit Us</h4>
                    <div class="text-sm space-y-4">
                        <p class="leading-relaxed">
                            Jl. Sulaiman No.12A 10,<br>
                            RT.10/RW.03, Sukabumi Utara<br>
                            Kec. Kebon Jeruk, Kota Jakarta Barat,<br>
                            Daerah Khusus Ibukota Jakarta 11540
                        </p>
                        <div class="mt-4">
                            <p class="font-medium text-xs mb-1">Opening Hours</p>
                            <p>Mon - Sunday: 08.00 - 20.00</p>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </footer>

    <a href="https://wa.me/6281311996099" target="_blank" class="fixed bottom-6 right-6 bg-[#25D366] text-white p-4 rounded-full shadow-2xl hover:bg-[#20b858] transition-transform hover:scale-110 z-50 flex items-center justify-center">
        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
        </svg>
    </a>

</body>
</html>