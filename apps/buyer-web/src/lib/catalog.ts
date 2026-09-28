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
  { id: 'all', label: '\u3059\u3079\u3066' },
  { id: 'vegetables', label: '\u91ce\u83dc' },
  { id: 'fruit', label: '\u679c\u7269' },
  { id: 'grains', label: '\u7c73\u30fb\u7a40\u7269' },
  { id: 'sets', label: '\u30bb\u30c3\u30c8' },
]

export const products: CatalogueProduct[] = [
  {
    id: 'seasonal-vegetable-set',
    name: '\u5b63\u7bc0\u306e\u91ce\u83dc\u30bb\u30c3\u30c8',
    category: 'sets',
    imageUrl:
      'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1080&q=80',
    price: 2682,
    regularPrice: 2980,
    discountRate: 10,
    producerName: '\u307f\u306e\u308a\u8fb2\u5712',
    description:
      '\u7523\u5730\u304b\u3089\u76f4\u63a5\u304a\u5c4a\u3051\u3059\u308b\u3001\u63a1\u308c\u305f\u3066\u65b0\u9bae\u306a\u65ec\u306e\u8fb2\u4f5c\u7269\u3092\u8a70\u3081\u305f\u5927\u6e80\u8db3\u306e\u91ce\u83dc\u30bb\u30c3\u30c8\u5546\u54c1\u3067\u3059\u3002',
    searchTerms: ['\u91ce\u83dc', '\u30bb\u30c3\u30c8'],
    variants: [
      { id: 'regular', name: '\u901a\u5e38\u30bb\u30c3\u30c8', price: 2682, stock: 12 },
      { id: 'large', name: '\u5927\u5bb9\u91cf', price: 3680, stock: 6 },
      { id: 'gift', name: '\u30ae\u30d5\u30c8', price: 3980, stock: 4 },
    ],
  },
  {
    id: 'minamisaki-tomato',
    name: '\u5357\u5d0e\u30c8\u30de\u30c8',
    category: 'vegetables',
    imageUrl:
      'https://images.unsplash.com/photo-1546094096-0df4bcaaa337?auto=format&fit=crop&w=1080&q=80',
    price: 1280,
    producerName: '\u5357\u5d0e\u8fb2\u5712',
    description:
      '\u967d\u3056\u3057\u3092\u305f\u3063\u3077\u308a\u6d74\u3073\u3066\u80b2\u3063\u305f\u3001\u7518\u307f\u3068\u9178\u5473\u306e\u30d0\u30e9\u30f3\u30b9\u304c\u826f\u3044\u30c8\u30de\u30c8\u3067\u3059\u3002',
    searchTerms: ['\u30c8\u30de\u30c8', '\u5357\u5d0e'],
    variants: [{ id: 'standard', name: '\u901a\u5e38', price: 1280, stock: 20 }],
  },
  {
    id: 'new-rice-5kg',
    name: '\u65b0\u7c73 5kg',
    category: 'grains',
    imageUrl:
      'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?auto=format&fit=crop&w=1080&q=80',
    price: 3800,
    producerName: '\u5c71\u7530\u7c73\u5e97',
    description:
      '\u9999\u308a\u9ad8\u304f\u3001\u3064\u3084\u306e\u3042\u308b\u65b0\u7c73\u3092\u6bce\u65e5\u306e\u98df\u5353\u3078\u304a\u5c4a\u3051\u3057\u307e\u3059\u3002',
    variants: [{ id: '5kg', name: '5kg', price: 3800, stock: 8 }],
  },
  {
    id: 'tomato-assortment',
    name: '\u30c8\u30de\u30c8\u8a70\u3081\u5408\u308f\u305b',
    category: 'vegetables',
    imageUrl:
      'https://images.unsplash.com/photo-1561136594-7f68413baa99?auto=format&fit=crop&w=1080&q=80',
    price: 2480,
    producerName: '\u5357\u5d0e\u8fb2\u5712',
    description:
      '\u5b8c\u719f\u30c8\u30de\u30c8\u3092\u305f\u3063\u3077\u308a\u8a70\u3081\u5408\u308f\u305b\u305f\u3001\u304a\u5f97\u306a\u30bb\u30c3\u30c8\u3067\u3059\u3002',
    searchTerms: ['\u30c8\u30de\u30c8'],
    variants: [{ id: 'box', name: '\u7bb1\u8a70\u3081', price: 2480, stock: 10 }],
  },
  {
    id: 'gift-set',
    name: '\u30ae\u30d5\u30c8\u30bb\u30c3\u30c8',
    category: 'sets',
    imageUrl:
      'https://images.unsplash.com/photo-1549465220-1a8b9238cd48?auto=format&fit=crop&w=1080&q=80',
    price: 4500,
    producerName: '\u307f\u306e\u308a\u8fb2\u5712',
    description:
      '\u5b63\u7bc0\u306e\u5473\u308f\u3044\u3092\u8a70\u3081\u5408\u308f\u305b\u305f\u3001\u8d08\u308a\u7269\u306b\u3074\u3063\u305f\u308a\u306e\u30bb\u30c3\u30c8\u3067\u3059\u3002',
    variants: [{ id: 'gift-standard', name: '\u30ae\u30d5\u30c8', price: 4500, stock: 5 }],
  },
  {
    id: 'seasonal-apple-box',
    name: '\u65ec\u306e\u308a\u3093\u3054\u30bb\u30c3\u30c8',
    category: 'fruit',
    imageUrl:
      'https://images.unsplash.com/photo-1568702846914-96b305d2aaeb?auto=format&fit=crop&w=1080&q=80',
    price: 2180,
    producerName: '\u307f\u306e\u308a\u8fb2\u5712',
    description:
      '\u307f\u305a\u307f\u305a\u3057\u3044\u65ec\u306e\u308a\u3093\u3054\u3092\u8a70\u3081\u5408\u308f\u305b\u305f\u30bb\u30c3\u30c8\u3067\u3059\u3002',
    searchTerms: ['\u308a\u3093\u3054', '\u679c\u7269'],
    variants: [{ id: 'standard', name: '\u901a\u5e38', price: 2180, stock: 15 }],
  },
]
