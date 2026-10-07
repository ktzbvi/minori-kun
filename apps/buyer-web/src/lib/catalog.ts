export type Category = 'all' | 'vegetables' | 'fruit' | 'grains' | 'sets'

export type ProductVariant = {
  id: string
  name: string
  price: number
  stock: number
}

export type CatalogueProduct = {
  id: string
  name: string
  category: Exclude<Category, 'all'>
  imageUrl: string
  price: number
  regularPrice?: number
  discountRate?: number
  producerName: string
  description: string
  searchTerms?: string[]
  variants: ProductVariant[]
}

export const categories: { id: Category; label: string }[] = [
  { id: 'all', label: 'すべて' },
  { id: 'vegetables', label: '野菜' },
  { id: 'fruit', label: '果物' },
  { id: 'grains', label: '米・穀物' },
  { id: 'sets', label: 'セット' },
]

export const products: CatalogueProduct[] = [
  {
    id: 'seasonal-vegetable-set',
    name: '季節の野菜セット',
    category: 'sets',
    imageUrl:
      'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1080&q=80',
    price: 2682,
    regularPrice: 2980,
    discountRate: 10,
    producerName: 'みのり農園',
    description:
      '産地から直接お届けする、採れたて新鮮な旬の農作物を詰めた大満足の野菜セット商品です。',
    searchTerms: ['野菜', 'セット'],
    variants: [
      { id: 'regular', name: '通常セット', price: 2682, stock: 12 },
      { id: 'large', name: '大容量', price: 3680, stock: 6 },
      { id: 'gift', name: 'ギフト', price: 3980, stock: 4 },
    ],
  },
  {
    id: 'minamisaki-tomato',
    name: '南崎トマト',
    category: 'vegetables',
    imageUrl:
      'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?auto=format&fit=crop&w=1080&q=80',
    price: 1280,
    producerName: '南崎農園',
    description:
      '陽ざしをたっぷり浴びて育った、甘みと酸味のバランスが良いトマトです。',
    searchTerms: ['トマト', '南崎'],
    variants: [{ id: 'standard', name: '通常', price: 1280, stock: 20 }],
  },
  {
    id: 'new-rice-5kg',
    name: '新米 5kg',
    category: 'grains',
    imageUrl:
      'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?auto=format&fit=crop&w=1080&q=80',
    price: 3800,
    producerName: '山田米店',
    description:
      '香り高く、つやのある新米を毎日の食卓へお届けします。',
    variants: [{ id: '5kg', name: '5kg', price: 3800, stock: 8 }],
  },
  {
    id: 'tomato-assortment',
    name: 'トマト詰め合わせ',
    category: 'vegetables',
    imageUrl:
      'https://images.unsplash.com/photo-1561136594-7f68413baa99?auto=format&fit=crop&w=1080&q=80',
    price: 2480,
    producerName: '南崎農園',
    description:
      '完熟トマトをたっぷり詰め合わせた、お得なセットです。',
    searchTerms: ['トマト'],
    variants: [{ id: 'box', name: '箱詰め', price: 2480, stock: 10 }],
  },
  {
    id: 'gift-set',
    name: 'ギフトセット',
    category: 'sets',
    imageUrl:
      'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=1080&q=80',
    price: 4500,
    producerName: 'みのり農園',
    description:
      '季節の味わいを詰め合わせた、贈り物にぴったりのセットです。',
    variants: [{ id: 'gift-standard', name: 'ギフト', price: 4500, stock: 5 }],
  },
  {
    id: 'seasonal-apple-box',
    name: '旬のりんごセット',
    category: 'fruit',
    imageUrl:
      'https://images.unsplash.com/photo-1568702846914-96b305d2aaeb?auto=format&fit=crop&w=1080&q=80',
    price: 2180,
    producerName: 'みのり農園',
    description:
      'みずみずしい旬のりんごを詰め合わせたセットです。',
    searchTerms: ['りんご', '果物'],
    variants: [{ id: 'standard', name: '通常', price: 2180, stock: 15 }],
  },
]
