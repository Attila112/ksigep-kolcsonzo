import { apiRequest } from '@/lib/api';

type Product = {
  id: number;
  name: string;
  description: string;
  price_per_day: number;
  deposit: number;
  reviews_count: number;
  average_rating: number | null;
  category: {
    id: number;
    name: string;
  };
};

type ProductListResponse = {
  products: Product[];
};

export default async function Home() {
  const data = await apiRequest<ProductListResponse>('/products');

  return (
    <main>
      <h1>Kisgép-kölcsönző</h1>

      {data.products.length === 0 ? (
        <p>Jelenleg nincs elérhető termék.</p>
      ) : (
        <ul>
          {data.products.map((product) => (
            <li key={product.id}>
              <strong>{product.name}</strong>
              <div>{product.category.name}</div>
              <div>
                {product.price_per_day.toLocaleString('hu-HU')} Ft/nap
              </div>
            </li>
          ))}
        </ul>
      )}
    </main>
  );
}