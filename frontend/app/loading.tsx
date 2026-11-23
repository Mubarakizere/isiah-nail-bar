export default function Loading() {
  return (
    <main className="flex min-h-[60vh] items-center justify-center bg-[#FAF7F2] px-5 text-[#221C1A]">
      <div className="text-center" role="status" aria-live="polite">
        <div className="mx-auto h-9 w-9 animate-spin rounded-full border-2 border-[#D8CDBD] border-t-[#9A7B4C]" />
        <p className="mt-4 text-sm text-[#756E67]">Preparing your sanctuary…</p>
      </div>
    </main>
  );
}
