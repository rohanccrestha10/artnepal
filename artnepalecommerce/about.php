<?php
/**
 * About Us Page
 * ARTNEPAL E-commerce Website
 * 
 * This page provides information about ARTNEPAL and Nepali culture
 */

// Include header
require_once 'includes/header.php';
?>

<div class="container">
    <!-- Hero Section -->
    <section style="background: linear-gradient(135deg, rgba(139,69,19,0.9), rgba(212,175,55,0.9)), url('assets/images/about-hero.jpg'); background-size: cover; background-position: center; color: white; padding: 4rem 0; text-align: center; margin-bottom: 3rem; border-radius: 10px;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">About ARTNEPAL</h1>
        <p style="font-size: 1.3rem; max-width: 800px; margin: 0 auto; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
            Preserving Nepal's Rich Cultural Heritage Through Traditional Arts and Handicrafts
        </p>
    </section>
    
    <!-- Our Story -->
    <section style="margin-bottom: 4rem;">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h2 style="color: #8B4513; font-size: 2rem; margin-bottom: 2rem;">Our Story</h2>
            <p style="color: #666; line-height: 1.8; margin-bottom: 1.5rem; font-size: 1.1rem;">
                ARTNEPAL supports local artisans across Nepal, helping preserve traditional crafts and connect them with the global community.
            </p>
            <p style="color: #666; line-height: 1.8; margin-bottom: 1.5rem; font-size: 1.1rem;">
                We noticed that many traditional Nepali crafts were at risk of being lost to modernization, with young generations moving away from traditional arts.
            </p>
            <p style="color: #666; line-height: 1.8; font-size: 1.1rem;">
                Today, ARTNEPAL serves as a bridge between Nepal's rich artistic heritage and the global community, ensuring that every purchase helps preserve centuries-old techniques and supports the families who have dedicated their lives to these crafts.
            </p>
        </div>
    </section>
    
    <!-- Nepali Culture Section -->
    <section style="background: #FFF8DC; padding: 3rem; border-radius: 10px; margin-bottom: 4rem;">
        <h2 style="color: #8B4513; font-size: 2rem; margin-bottom: 2rem; text-align: center;">Nepali Cultural Heritage</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🏛️</div>
                <h3 style="color: #8B4513; margin-bottom: 1rem;">Ancient Traditions</h3>
                <p style="color: #666;">
                    Nepal's artistic traditions date back over 2,000 years, influenced by Hinduism, Buddhism, and indigenous cultures. 
                    Each piece carries the wisdom of generations.
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🎨</div>
                <h3 style="color: #8B4513; margin-bottom: 1rem;">Diverse Art Forms</h3>
                <p style="color: #666;">
                    From Thangka paintings to wood carvings, from pottery to metalwork, Nepal offers an incredible diversity of 
                    traditional arts, each with its unique story and technique.
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🙏</div>
                <h3 style="color: #8B4513; margin-bottom: 1rem;">Spiritual Significance</h3>
                <p style="color: #666;">
                    Many Nepali arts are deeply connected to spiritual practices. Buddhist Thangkas, Hindu deity statues, and 
                    ceremonial masks serve both artistic and religious purposes.
                </p>
            </div>
            
            <div style="text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🌍</div>
                <h3 style="color: #8B4513; margin-bottom: 1rem;">Cultural Crossroads</h3>
                <p style="color: #666;">
                    Situated between India and Tibet, Nepal's art reflects influences from both regions while maintaining 
                    its unique identity, creating a distinctive cultural tapestry.
                </p>
            </div>
        </div>
    </section>
    
    <!-- Traditional Handicrafts -->
    <section style="margin-bottom: 4rem;">
        <h2 style="color: #8B4513; font-size: 2rem; margin-bottom: 2rem; text-align: center;">Traditional Handicrafts</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">🏺 Clay Pottery</h3>
                <p style="color: #666; margin-bottom: 1rem;">
                    Traditional Nepali pottery is made using ancient techniques passed down through generations. 
                    From cooking pots to decorative vases, each piece is handcrafted and fired in traditional kilns.
                </p>
                <p style="color: #666; font-size: 0.9rem;">
                    <strong>Regions:</strong> Bhaktapur, Thimi, Kathmandu Valley
                </p>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">🖼️ Thangka Paintings</h3>
                <p style="color: #666; margin-bottom: 1rem;">
                    Intricate Buddhist paintings on cotton or silk, Thangkas depict deities, mandalas, and spiritual scenes. 
                    Each painting can take months to complete and is considered a sacred art form.
                </p>
                <p style="color: #666; font-size: 0.9rem;">
                    <strong>Regions:</strong> Boudha, Swayambhu, Patan
                </p>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">🪵 Wood Carving</h3>
                <p style="color: #666; margin-bottom: 1rem;">
                    Nepali wood carving is renowned for its intricate designs and religious motifs. From window frames to 
                    furniture, each piece showcases exceptional craftsmanship and attention to detail.
                </p>
                <p style="color: #666; font-size: 0.9rem;">
                    <strong>Regions:</strong> Patan, Bhaktapur, Kathmandu
                </p>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">🎭 Cultural Masks</h3>
                <p style="color: #666; margin-bottom: 1rem;">
                    Traditional masks used in religious ceremonies and festivals, these colorful creations represent various 
                    deities and demons. Each mask has specific spiritual significance and is used in traditional dances.
                </p>
                <p style="color: #666; font-size: 0.9rem;">
                    <strong>Regions:</strong> Kathmandu Valley, Pokhara, Bhaktapur
                </p>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">🗿 Metal Statues</h3>
                <p style="color: #666; margin-bottom: 1rem;">
                    Bronze and brass statues of Hindu and Buddhist deities are created using the lost-wax casting technique. 
                    These sacred objects are used in temples and homes for worship.
                </p>
                <p style="color: #666; font-size: 0.9rem;">
                    <strong>Regions:</strong> Patan, Kathmandu, Boudha
                </p>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1rem;">🧵 Textile Arts</h3>
                <p style="color: #666; margin-bottom: 1rem;">
                    Traditional weaving and embroidery create beautiful textiles including pashmina shawls, dhaka fabrics, 
                    and traditional clothing. Each pattern tells a story of Nepal's diverse ethnic groups.
                </p>
                <p style="color: #666; font-size: 0.9rem;">
                    <strong>Regions:</strong> Kathmandu, Pokhara, Eastern Nepal
                </p>
            </div>
        </div>
    </section>
    
    <!-- Our Mission -->
    <section style="background: linear-gradient(135deg, #8B4513, #A0522D); color: white; padding: 3rem; border-radius: 10px; margin-bottom: 4rem;">
        <h2 style="font-size: 2rem; margin-bottom: 2rem; text-align: center;">Our Mission & Vision</h2>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">
            <div>
                <h3 style="color: #D4AF37; margin-bottom: 1rem;">🎯 Our Mission</h3>
                <ul style="line-height: 1.8; padding-left: 1.5rem;">
                    <li>Preserve and promote Nepal's traditional arts and handicrafts</li>
                    <li>Provide fair wages and sustainable income for local artisans</li>
                    <li>Create awareness about Nepali cultural heritage globally</li>
                    <li>Ensure traditional techniques are passed to future generations</li>
                    <li>Maintain quality and authenticity in all our products</li>
                </ul>
            </div>
            
            <div>
                <h3 style="color: #D4AF37; margin-bottom: 1rem;">🔭 Our Vision</h3>
                <ul style="line-height: 1.8; padding-left: 1.5rem;">
                    <li>To become the leading platform for authentic Nepali handicrafts</li>
                    <li>Establish Nepal as a global hub for traditional arts</li>
                    <li>Create sustainable livelihoods for 1,000+ artisan families</li>
                    <li>Preserve 50+ endangered traditional art forms</li>
                    <li>Build cultural bridges through art and craftsmanship</li>
                </ul>
            </div>
        </div>
    </section>
    
    <!-- Team Section -->
    <section style="margin-bottom: 4rem;">
        <h2 style="color: #8B4513; font-size: 2rem; margin-bottom: 2rem; text-align: center;">Meet Our Team</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div style="text-align: center;">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #8B4513, #D4AF37); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                    👨‍💼
                </div>
                <h3 style="color: #8B4513; margin-bottom: 0.5rem;">Rohan Shrestha</h3>
                <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Founder & CEO</p>
                <p style="color: #666; font-size: 0.85rem;">Leading ARTNEPAL's mission to preserve Nepal's cultural heritage through traditional arts and crafts.</p>
            </div>
            
            <div style="text-align: center;">
                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, #8B4513, #D4AF37); border-radius: 50%; margin: 0 auto 1rem; display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                    🛍️
                </div>
                <h3 style="color: #8B4513; margin-bottom: 0.5rem;">Sujit Thakur</h3>
                <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;">Operations Manager</p>
                <p style="color: #666; font-size: 0.85rem;">Managing online operations and ensuring smooth delivery of authentic Nepali handicrafts to customers worldwide.</p>
            </div>
        </div>
    </section>
    
    <!-- Get in Touch Section -->
    <section style="margin-bottom: 4rem;">
        <h2 style="color: #8B4513; font-size: 2rem; margin-bottom: 2rem; text-align: center;">Get in Touch</h2>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Contact Information -->
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1.5rem;">📞 Contact Numbers</h3>
                <div style="space-y: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Main:</strong>
                        <div style="color: #666; font-size: 1rem;">+977-9860146269</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Sales:</strong>
                        <div style="color: #666; font-size: 1rem;">+977-9818163312</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Support:</strong>
                        <div style="color: #666; font-size: 1rem;">+977-9861251415</div>
                    </div>
                </div>
            </div>
            
            <!-- Payment & Delivery -->
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1.5rem;">💳 Payment & Delivery</h3>
                <div style="space-y: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Payment Method:</strong>
                        <div style="color: #666; font-size: 1rem;">Cash on Delivery (COD) Only</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Delivery Areas:</strong>
                        <div style="color: #666; font-size: 1rem;">Kathmandu Valley & Major Cities</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Delivery Time:</strong>
                        <div style="color: #666; font-size: 1rem;">3-7 business days</div>
                    </div>
                </div>
            </div>
            
            <!-- Online Store -->
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                <h3 style="color: #8B4513; margin-bottom: 1.5rem;">🌐 Online Store</h3>
                <div style="space-y: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Service Type:</strong>
                        <div style="color: #666; font-size: 1rem;">Online-Only Business</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Operating Hours:</strong>
                        <div style="color: #666; font-size: 1rem;">24/7 Online Shopping</div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <strong style="color: #333;">Customer Support:</strong>
                        <div style="color: #666; font-size: 1rem;">10 AM - 7 PM (Daily)</div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Call to Action -->
    <section style="background: linear-gradient(135deg, #D4AF37, #FFD700); padding: 3rem; border-radius: 10px; text-align: center;">
        <h2 style="color: #8B4513; font-size: 2rem; margin-bottom: 1rem;">Join Us in Preserving Nepal's Heritage</h2>
        <p style="color: #8B4513; font-size: 1.1rem; margin-bottom: 2rem; max-width: 600px; margin-left: auto; margin-right: auto;">
            Every purchase you make directly supports artisan families and helps keep traditional arts alive for future generations.
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <a href="products.php" class="btn btn-primary" style="background-color: #8B4513; color: white; padding: 1rem 2rem;">
                Shop Traditional Arts
            </a>
            <a href="contact.php" class="btn btn-secondary" style="background-color: white; color: #8B4513; padding: 1rem 2rem;">
                Partner With Us
            </a>
        </div>
    </section>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>
